<?php

namespace Oktave;

use Oktave\Resources\BlacklistItems;
use Oktave\Resources\Campaigns;
use Oktave\Resources\Webhooks;

/**
 * Class Client
 *
 * @package Oktave
 *
 * @property Campaigns      $campaigns
 * @property BlacklistItems $blacklistItems
 * @property Webhooks       $webhooks
 */
class Client
{
    /**
     * @var string Oktave PHP SDK user agent
     */
    const UA = 'oktave-php-sdk';

    /**
     * @var string Oktave PHP SDK version
     */
    const VERSION = 'v1';

    /**
     * @var string Oktave API endpoint
     */
    private $base = 'https://api.oktave.com';

    /**
     * @var string Oktave auth URI
     */
    private $authURI = 'api/token';

    /**
     * @var string Oktave Oauth 2 Client credentials ID
     */
    private $client_id;

    /**
     * @var string Oktave Oauth 2 Client credentials secret
     */
    private $client_secret;

    /**
     * @var string Oktave webhook secret
     */
    private $webhook_secret;

    /**
     * @var string|null Current Oktave Team
     */
    private $team;

    /**
     *  __get overloads the client with a property that will check if there is a resource for the given $method
     *  which allows calls such as $oktave->products->get() to be correctly routed to the appropriate handler
     *
     * @param  string  $method
     *
     * @return Resource
     * @throws Exceptions\InvalidResourceException
     */
    public function __get(string $method): Resource
    {
        $potentialEndpointClass = 'Oktave\Resources\\'.ucfirst($method);
        if (class_exists($potentialEndpointClass)) {
            // construct a resource object and pass in this client
            $resource = new $potentialEndpointClass($this);
            return $resource;
        }

        $trace = debug_backtrace();
        $message = 'Undefined property via __get(): '.$method.' in '.$trace[0]['file'].' on line '.$trace[0]['line'];
        throw new Exceptions\InvalidResourceException($message);
    }

    /**
     *  Create an instance of the SDK, passing in a configuration for it to set up
     *
     * @param  array initial config
     *
     */
    public function __construct($config = [])
    {
        if (isset($config['client_id'])) {
            $this->setClientID($config['client_id']);
        }
        if (isset($config['client_secret'])) {
            $this->setClientSecret($config['client_secret']);
        }
        if (isset($config['webhook_secret'])) {
            $this->setWebhookSecret($config['webhook_secret']);
        }
        if (isset($config['api_endpoint'])) {
            $this->setBaseURL($config['api_endpoint']);
        }
        if (isset($config['team'])) {
            $this->setTeam($config['team']);
        }
    }

    /**
     *  Set a custom base URL to access the API (for enterprise customers)
     *
     * @param  string  $base  the base URL (fully qualified, eg 'https://api.yourcompany.com')
     *
     * @return $this
     */
    public function setBaseURL(string $base): self
    {
        $this->base = rtrim($base, '/');
        return $this;
    }

    /**
     *  Get the authentication endpoint
     *
     * @return string the FQDN with URI for authentication requests
     */
    public function getAuthEndpoint(): string
    {
        return $this->getBase().'/'.$this->getAuthURI();
    }

    /**
     *  Get the API endpoint for non authentication calls
     *
     * @param  string  $uri  is the uri to append to the API endpoint
     *
     * @return string the FQDN with URI for API requests
     */
    public function getAPIEndpoint(string $uri = ''): string
    {
        return $this->getBase().'/'.($uri ?? '');
    }

    /**
     *  Get the base URL
     *
     * @return string
     */
    public function getBase(): string
    {
        return $this->base;
    }

    /**
     *  Get the URI to authenticate against
     *
     * @return string
     */
    public function getAuthURI(): string
    {
        return $this->authURI;
    }

    /**
     *  Get the client_id
     */
    public function getClientID(): string
    {
        return $this->client_id;
    }

    /**
     *  Get the client_secret
     */
    public function getClientSecret(): string
    {
        return $this->client_secret;
    }

    /**
     *  Set the client_id for authentication calls
     *
     * @param  string the client_id
     *
     * @return $this
     */
    public function setClientID(string $client_id): self
    {
        $this->client_id = $client_id;
        return $this;
    }

    /**
     *  Set the client_secret for authentication calls
     *
     * @param  string the secret
     *
     * @return $this
     */
    public function setClientSecret(string $secret): self
    {
        $this->client_secret = $secret;
        return $this;
    }

    /**
     * Get the webhook_secret for Oktave Webhook origin validation
     *
     * @return string|null
     */
    public function getWebhookSecret(): ?string
    {
        return $this->webhook_secret;
    }

    /**
     * Set the current Oktave team for API calls
     *
     * @param  string|null  $teamId
     *
     * @return Client
     */
    public function setTeam(?string $teamId = null): Client
    {
        $this->team = $teamId;
        return $this;
    }

    /**
     * Get the current Oktave team for API calls
     *
     * @return string|null
     */
    public function getTeam(): ?string
    {
        return $this->team;
    }

    /**
     * Set the webhook_secret for Oktave Webhook origin validation
     *
     * @param  string  $webhookSecret
     *
     * @return Client
     */
    public function setWebhookSecret(string $webhookSecret): Client
    {
        $this->webhook_secret = $webhookSecret;
        return $this;
    }
}
