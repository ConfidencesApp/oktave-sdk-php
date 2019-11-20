<?php

namespace Oktave\SDK\Tests\Resources;

use Mockery;
use Oktave;
use PHPUnit\Framework\TestCase;

class WebhooksTest extends TestCase
{
    public function testThrowExceptionIfNoWebhookSecretProvided(): void
    {
        $client = Mockery::mock(Oktave\Client::class);
        $client->shouldReceive('getWebhookSecret')->andReturn(null);

        $this->expectException(Oktave\Exceptions\InvalidConfigurationException::class);

        $webhooks = new Oktave\Resources\Webhooks($client);
        $webhooks->verifySignature('event-id', time(), 'signature');
    }

    public function testThrowExceptionIfInvalidEventID(): void
    {
        $client = Mockery::mock(Oktave\Client::class);
        $client->shouldReceive('getWebhookSecret')->andReturn('secret');

        $this->expectException(Oktave\Exceptions\ValidationException::class);
        $this->expectErrorMessage('The "eventID" value is missing.');

        $webhooks = new Oktave\Resources\Webhooks($client);
        $webhooks->verifySignatureFromGlobals();
    }

    public function testThrowExceptionIfInvalidRequestTimestamp(): void
    {
        $client = Mockery::mock(Oktave\Client::class);
        $client->shouldReceive('getWebhookSecret')->andReturn('secret');

        $this->expectException(Oktave\Exceptions\ValidationException::class);
        $this->expectErrorMessage('The "requestTimestamp" value is missing or invalid.');

        $_SERVER['HTTP_OKTAVE_EVENT_ID'] = 'event-id';

        $webhooks = new Oktave\Resources\Webhooks($client);
        $webhooks->verifySignatureFromGlobals();
    }

    public function testThrowExceptionIfInvalidSignature(): void
    {
        $client = Mockery::mock(Oktave\Client::class);
        $client->shouldReceive('getWebhookSecret')->andReturn('secret');

        $this->expectException(Oktave\Exceptions\ValidationException::class);
        $this->expectErrorMessage('The "signature" value is missing.');

        $_SERVER['HTTP_OKTAVE_EVENT_ID'] = 'event-id';
        $_SERVER['HTTP_OKTAVE_TIMESTAMP'] = time();

        $webhooks = new Oktave\Resources\Webhooks($client);
        $webhooks->verifySignatureFromGlobals();
    }

    public function testReturnFalseIfSignatureIsInvalid(): void
    {
        $client = Mockery::mock(Oktave\Client::class);
        $client->shouldReceive('getWebhookSecret')->andReturn('secret');

        $_SERVER['HTTP_OKTAVE_EVENT_ID'] = 'event-id';
        $_SERVER['HTTP_OKTAVE_TIMESTAMP'] = time();
        $_SERVER['HTTP_OKTAVE_SIGNATURE'] = 'signature';

        $webhooks = new Oktave\Resources\Webhooks($client);
        $this->assertFalse($webhooks->verifySignatureFromGlobals());
    }

    public function testReturnTrueIfSignatureIsValid(): void
    {
        $webhookSecret = 'secret';
        $eventID = 'event-id';
        $requestTimestamp = time();
        $signature = hash_hmac(Oktave\Resources\Webhooks::HMAC_HASH_ALGO, $eventID.$requestTimestamp, $webhookSecret);

        $client = Mockery::mock(Oktave\Client::class);
        $client->shouldReceive('getWebhookSecret')->andReturn($webhookSecret);


        $_SERVER['HTTP_OKTAVE_EVENT_ID'] = $eventID;
        $_SERVER['HTTP_OKTAVE_TIMESTAMP'] = $requestTimestamp;
        $_SERVER['HTTP_OKTAVE_SIGNATURE'] = $signature;

        $webhooks = new Oktave\Resources\Webhooks($client);
        $this->assertTrue($webhooks->verifySignatureFromGlobals());
    }
}
