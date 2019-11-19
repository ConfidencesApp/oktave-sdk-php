<?php

namespace Oktave;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\MultipartStream as MultipartStream;
use Oktave\Client as Client;

class Request
{

    /**
     * @var bool|HttpClient an HTTP client to execute the API calls (guzzle)
     */
    private $httpClient;

    /**
     * @var string the request method GET|POST|PUT|PATCH|DELETE
     */
    private $method;

    /**
     * @var string the URL
     */
    private $url;

    /**
     * @var array request headers
     */
    private $headers = [];

    /**
     * @var array request body
     */
    private $body = [];

    /**
     * @var array URL params
     */
    private $params = [];

    /**
     * @var null|Response URL params
     */
    private $response;

    /**
     * Create an instance of Oktave Request, passing in a HTTP client
     *
     * @param  ClientInterface|null  $client
     */
    public function __construct(?ClientInterface $client = null)
    {
        $this->httpClient = $client ?? new HttpClient();
    }

    /**
     * @param  string  $method  the request method for the call
     *
     * @return Request
     * @throws Exceptions\InvalidRequestMethod
     */
    public function setMethod(string $method): self
    {
        $method = strtoupper(trim($method));

        if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
            throw new Exceptions\InvalidRequestMethod;
        }

        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method ?? 'GET';
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     *  Get a specific header
     *
     * @param  string  $name  the header name
     *
     * @return null|string false if the header is not set, otherwise the string value
     */
    private function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     *  Clear request headers
     *
     * @return $this
     */
    public function clearHeaders(): self
    {
        $this->headers = [];
        return $this;
    }

    /**
     *  Add headers to the request
     *
     * @param  array  $headers  an array of $name => $value headers to set
     *
     * @return $this
     */
    public function addHeaders(array $headers = []): self
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }
        return $this;
    }

    /**
     *  Add (over overwrite) a single header to the request
     *
     * @param  string  $name   the header name
     * @param  string  $value  the header value
     *
     * @return $this
     */
    public function addHeader(string $name, string $value = ''): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     *  Set the default request headers
     *
     * @return $this
     */
    private function setDefaultHeaders(): self
    {
        $defaultHeaders = [
            'Content-Type'          => 'application/json',
            'Accept'                => 'application/json',
            'User-Agent'            => Client::UA,
            'X-Oktave-SDK-Language' => 'php',
            'X-Oktave-SDK-Version'  => Client::VERSION,
        ];

        foreach ($defaultHeaders as $name => $value) {
            if ($this->getHeader($name) === null) {
                $this->addHeader($name, $value);
            }
        }

        return $this;
    }

    /**
     *  Set the body of the request
     *
     * @param  array  $body
     *
     * @return $this
     */
    public function setBody(array $body = []): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     *  Get the request body
     *
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     *  Get the request URL
     *
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     *  Set the request URL
     *
     * @param  string  $url  the URL this request should hit
     *
     * @return $this
     */
    public function setURL(string $url): self
    {
        $this->url = trim($url);
        return $this;
    }

    /**
     * @return array
     * @throws Exceptions\InvalidContentType
     */
    public function getPayload(): array
    {
        $payload = [];
        $body = $this->getBody();
        if (!empty($body)) {
            $payload[$this->getBodyKey()] = $body;
        }
        $payload['headers'] = $this->getHeaders();
        $params = $this->getQueryStringParams();
        if (!empty($params)) {
            $payload['query'] = $params;
        }

        // when sending multipart, specify our boundary and stream the data
        if ($this->getHeader('Content-Type') === 'multipart/form-data') {
            $payload = $this->prepareMultipartPayload($payload);
        }

        return $payload;
    }

    /**
     * Apply the correct header for multipart requests
     *
     * @param  array  $payload
     *
     * @return array
     */
    public function prepareMultipartPayload(array $payload): array
    {
        // generate a random boundary
        $boundary = 'oktave_file_upload_'.rand(50000, 60000);

        // specify the boundary in the content type header
        $contentType = 'multipart/form-data; boundary='.$boundary;
        $this->addHeader('Content-Type', $contentType);
        $payload['headers']['Content-Type'] = $contentType;

        // remove the multipart 
        $payload['body'] = new MultipartStream($payload['body'], $boundary);

        return $payload;
    }

    /**
     *  Set the query string params
     *
     * @param  array  $params
     *
     * @return $this
     */
    public function setQueryStringParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    /**
     *  Get the query string params
     *
     * @return array
     */
    public function getQueryStringParams(): array
    {
        return $this->params;
    }

    /**
     *  Get the payload key for the body depending on whether the call is JSON/multipart
     *
     * @return string
     * @throws Exceptions\InvalidContentType
     */
    public function getBodyKey(): string
    {
        switch ($this->getHeader('Content-Type')) {
            case 'application/json':
                return 'json';
                break;
            case 'application/x-www-form-urlencoded':
                return 'form_params';
                break;
            case 'multipart/form-data':
                return 'body';
                break;
            default:
                throw new Exceptions\InvalidContentType;
        }
    }

    /**
     * @return null|Response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     *  Make a request
     *
     * @param  string|null  $resource
     *
     * @return $this
     * @throws Exceptions\InvalidContentType
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function make(string $resource = null): self
    {
        $this->setDefaultHeaders();

        $startTime = microtime(true);
        $result = $this->httpClient->request($this->getMethod(), $this->getURL(), $this->getPayload());
        $endTime = microtime(true);

        $this->response = new Response();
        $this->response->setExecutionTime(round(($endTime - $startTime), 5))
            ->setStatusCode($result->getStatusCode());

        // set the request ID for remote debugging if it is present            
        if (!empty(($requestID = $result->getHeader('X-Oktave-Request-Id')))) {
            $this->response->setRequestID($requestID[0]);
        }

        $body = json_decode($result->getBody());
        $this->response->setResource($resource)->setRaw($body)->parse();

        return $this;
    }

}
