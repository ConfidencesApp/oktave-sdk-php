<?php

namespace Oktave;

use Oktave\Interfaces\Storage;

class Resource
{
    /**
     * @var null|Client
     */
    private $client;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var Request
     */
    private $requestLib;

    /**
     * @var null|string
     */
    private $sort;

    /**
     * @var int
     */
    private $limit = 0;

    /**
     * @var int
     */
    private $offset = 0;

    /**
     * @var array
     */
    private $includes = [];

    /**
     * @var null|string The resource name for collection
     */
    protected $resourceCollection;

    /**
     * @var null|string The resource name for individual item
     */
    protected $resource;

    /**
     *  Create and return a new Resource
     *
     * @param  Client        $client   the Oktave\Client to use for calls
     * @param  null|Request
     * @param  null|Storage  $storage  a concrete implementation of the storage
     *
     * @return $this
     */
    public function __construct(Client $client, ?Request $requestLib = null, ?Storage $storage = null)
    {
        $this->client = $client;
        $this->requestLib = $requestLib ?? new Request;
        $this->storage = $storage ?? new Session;
        return $this;
    }

    /**
     *  Get the storage implementation
     *
     * @return Storage concrete implementation
     */
    public function getStorage(): Storage
    {
        return $this->storage;
    }

    /**
     *  Get the storage implementation
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     *  Set the included resources to request
     *
     * @param  array  $includes  the included resource type(s) eg ['products'], ['products', 'categories']
     *
     * @return $this
     */
    public function with(array $includes = [])
    {
        foreach ($includes as $include) {
            $this->includes[] = strtolower(trim($include));
        }
        return $this;
    }

    /**
     *  Adds a sort parameter to the request (eg `-name` or `name,-slug`)
     *
     * @param  null|string  $sort
     *
     * @return $this
     */
    public function sort(?string $sort = null): self
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     *  Get the resource sort
     *
     * @return null|string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     *  Set a limit on the number of resources
     *
     * @param  int  $limit
     *
     * @return $this
     */
    public function limit(int $limit = 0): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     *  Get the resource limit
     *
     * @return int
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     *  Set an offset on the resources
     *
     * @param  int  $offset
     *
     * @return $this
     */
    public function offset(int $offset = 0): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     *  Get the resource offset
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     *  Get the resource offset
     *
     * @return string
     */
    public function getResourceURI(): string
    {
        return $this->resourceCollection ? 'api/'.$this->resourceCollection : 'data';
    }

    /**
     *  Get a resource
     *
     * @param  string  $id  the ID of the resource
     *
     * @return Response
     * @throws Exceptions\AuthenticationException
     * @throws Exceptions\InvalidContentType
     * @throws Exceptions\InvalidRequestMethod
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $id): Response
    {
        return $this->call('get', [], $id);
    }

    /**
     *  Get resources
     *
     * @return Response
     * @throws Exceptions\AuthenticationException
     * @throws Exceptions\InvalidContentType
     * @throws Exceptions\InvalidRequestMethod
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function all(): Response
    {
        return $this->call('get');
    }

    /**
     *  Delete a resource
     *
     * @param  string the ID of the resource to delete
     *
     * @return Resource
     * @throws Exceptions\AuthenticationException
     * @throws Exceptions\InvalidContentType
     * @throws Exceptions\InvalidRequestMethod
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete($id): Response
    {
        return $this->call('delete', [], $id);
    }

    /**
     *  Update a resource
     *
     * @param  string  $id    the UUID of the resource to update
     * @param  array   $data  the data to update the resource with
     *
     * @return Response
     * @throws Exceptions\AuthenticationException
     * @throws Exceptions\InvalidContentType
     * @throws Exceptions\InvalidRequestMethod
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function update(string $id, ?array $data = []): Response
    {
        return $this->call('put', $data, $id);
    }

    /**
     *  Create a new resource
     *
     * @param  array  $data  the data to create the resource with
     *
     * @return Response
     * @throws Exceptions\AuthenticationException
     * @throws Exceptions\InvalidContentType
     * @throws Exceptions\InvalidRequestMethod
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(?array $data = []): Response
    {
        return $this->call('post', ['data' => $data]);
    }

    /**
     *  Get an access token from the local storage if available, otherwise request one from the API
     *
     * @return string the access token
     * @throws Exceptions\AuthenticationException
     * @throws Exceptions\InvalidContentType
     * @throws Exceptions\InvalidRequestMethod
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAccessToken(): string
    {
        // check in the session
        $existing = $this->storage->getKey('authentication');

        // is it still valid
        if ($existing && $existing->expires > time()) {
            return $existing->access_token;
        }

        // make the call to the API
        $authResponse = $this->makeAuthenticationCall();
        $data = $authResponse->getRaw();

        // save the access token result
        $this->storage->setKey('authentication', (object) [
            'access_token' => $data->access_token,
            'expires'      => $data->expires_in + time(),
        ]);

        // return the token
        return $data->access_token;
    }

    /**
     *  Get an access token from the API
     *
     * @return Response
     * @throws Exceptions\AuthenticationException
     * @throws Exceptions\InvalidContentType
     * @throws Exceptions\InvalidRequestMethod
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function makeAuthenticationCall(): Response
    {
        $authResponse = $this->call('POST', [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->client->getClientID(),
            'client_secret' => $this->client->getClientSecret(),
        ], null, null, null, false);

        if (empty($authResponse->getRaw()->access_token)) {
            throw new Exceptions\AuthenticationException;
        }

        return $authResponse;
    }

    /**
     *  Make a call to the API
     *
     * @param  string  $method                request method to use GET|POST|PUT|PATCH|DELETE
     * @param  array   $body                  any body data to send with the request
     * @param  string  $id                    any additional URI components as a string (eg 'relationships/categories')
     * @param  array   $headers               any specific headers for the request
     * @param  bool    $buildQueryParams      should we build query params (sort, limit etc)
     * @param  bool    $isAuthenticationCall  true if the call requires authentication (true for all calls except auth)
     *
     * @return Response
     * @throws Exceptions\AuthenticationException
     * @throws Exceptions\InvalidContentType
     * @throws Exceptions\InvalidRequestMethod
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function call(
        $method,
        ?array $body = [],
        ?string $id = null,
        ?array $headers = [],
        ?bool $buildQueryParams = true,
        ?bool $isAuthenticationCall = true
    ): Response {

        $url = $isAuthenticationCall ? $this->client->getAPIEndpoint($this->getResourceURI()) : $this->client->getAuthEndpoint();

        if ($id !== null) {
            $url = $url.'/'.$id;
        }

        $request = clone $this->requestLib;
        $request->setURL($url)
            ->setMethod($method)
            ->addHeaders($headers ?? [])
            ->setBody($body);

        if ($buildQueryParams) {
            $request->setQueryStringParams($this->buildQueryStringParams());
        }

        if ($isAuthenticationCall) {
            $request->addHeader('Authorization', 'Bearer '.$this->getAccessToken());
        }

        return $request->make($id !== null ? $this->resource : $this->resourceCollection)->getResponse();
    }

    /**
     *  Build the query string parameters based on the resource settings
     *
     * @return array
     */
    public function buildQueryStringParams(): array
    {
        $params = [];

        if ($this->limit > 0) {
            $params['page']['limit'] = $this->limit;
        }
        if ($this->offset > 0) {
            $params['page']['offset'] = $this->offset;
        }
        if ($this->sort) {
            $params['sort'] = $this->sort;
        }

        return $params;
    }

}
