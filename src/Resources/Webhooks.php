<?php

namespace Oktave\Resources;

use Oktave\Client;
use Oktave\Exceptions\InvalidConfigurationException;
use Oktave\Exceptions\ValidationException;
use Oktave\Resource;

class Webhooks extends Resource
{
    const HMAC_HASH_ALGO = 'sha256';

    /**
     * {@inheritDoc}
     */
    public $resourceCollection = 'webhooks';

    /**
     * {@inheritDoc}
     */
    public $resource = 'webhook';

    /**
     * @var Client
     */
    private $client;

    /**
     *  Create and return a new Webhooks instance
     *
     * @param  Client  $client  the Oktave\Client to use for calls
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Verify the webhook request signature from Oktave with provided parameters
     *
     * @param  string  $eventID           the webhook event ID
     * @param  int     $requestTimestamp  the webhook request timestamp
     * @param  string  $signature         the webhook request signature
     *
     * @return bool true if the request signature is valid
     * @throws InvalidConfigurationException when the required  "webhook_request" configuration is missing.
     */
    public function verifySignature(string $eventID, int $requestTimestamp, string $signature): bool
    {
        $webhookSecret = $this->client->getWebhookSecret();

        if (!$webhookSecret) {
            throw new InvalidConfigurationException('Webhook secret is required for webhook validation');
        }

        return hash_equals(hash_hmac(self::HMAC_HASH_ALGO, $eventID.$requestTimestamp, $webhookSecret), $signature);
    }

    /**
     * Verify the webhook request signature from Oktave from PHP globals ($_SERVER)
     *
     * @return bool true if the request signature is valid
     * @throws InvalidConfigurationException when the required  "webhook_request" configuration is missing.
     * @throws ValidationException when a required parameter is missing.
     */
    public function verifySignatureFromGlobals()
    {
        $eventID = isset($_SERVER['HTTP_OKTAVE_EVENT_ID']) ? $_SERVER['HTTP_OKTAVE_EVENT_ID'] : null;
        $requestTimestamp = isset($_SERVER['HTTP_OKTAVE_TIMESTAMP']) ? (int) $_SERVER['HTTP_OKTAVE_TIMESTAMP'] : null;
        $signature = isset($_SERVER['HTTP_OKTAVE_SIGNATURE']) ? $_SERVER['HTTP_OKTAVE_SIGNATURE'] : null;

        if (!$eventID) {
            throw new ValidationException('The "eventID" value is missing.');
        }
        if (!$requestTimestamp) {
            throw new ValidationException('The "requestTimestamp" value is missing or invalid.');
        }
        if (!$signature) {
            throw new ValidationException('The "signature" value is missing.');
        }

        return $this->verifySignature($eventID, $requestTimestamp, $signature);
    }
}
