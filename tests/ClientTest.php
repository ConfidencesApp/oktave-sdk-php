<?php

namespace Oktave\SDK\Tests;

use Oktave;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Oktave\Client
     */
    private $underTest;

    private $initialConfig = [
        'client_id'      => 'abc',
        'client_secret'  => '123',
        'api_endpoint'   => 'https://api-test.oktave.co',
        'webhook_secret' => 'secret',
        'team_id'        => '44395255-6d04-4187-a07f-706fa8a9c901',
    ];

    public function setUp(): void
    {
        $this->underTest = new Oktave\Client($this->initialConfig);
    }

    public function testSetUpConfiguresClientID(): void
    {
        $this->assertEquals($this->initialConfig['client_id'], $this->underTest->getClientID());
    }

    public function testSetUpConfiguresClientSecret(): void
    {
        $this->assertEquals($this->initialConfig['client_secret'], $this->underTest->getClientSecret());
    }

    public function testSetUpConfiguresWebhookSecret(): void
    {
        $this->assertEquals($this->initialConfig['webhook_secret'], $this->underTest->getWebhookSecret());
    }

    public function testGetBase(): void
    {
        $this->assertEquals($this->underTest->getBase(), 'https://api-test.oktave.co');
    }

    public function testGetAuthURI(): void
    {
        $this->assertEquals($this->underTest->getAuthURI(), 'api/token');
    }

    public function testGetAuthEndpoint(): void
    {
        $this->assertEquals($this->underTest->getAuthEndpoint(), 'https://api-test.oktave.co/api/token');
    }

    public function testGetAPIEndpoint(): void
    {
        $this->assertEquals($this->underTest->getAPIEndpoint(), 'https://api-test.oktave.co/');
    }

    public function testGetTeamId(): void
    {
        $this->assertEquals($this->underTest->getTeamId(), '44395255-6d04-4187-a07f-706fa8a9c901');
    }

    public function testResetTeamId(): void
    {
        $this->assertEquals($this->underTest->getTeamId(), '44395255-6d04-4187-a07f-706fa8a9c901');
        $this->underTest->setTeamId(null);
        $this->assertEquals($this->underTest->getTeamId(), null);
    }

    public function testGetAPIEndpointWithURIReturnsCorrectURL(): void
    {
        $this->assertEquals($this->underTest->getAPIEndpoint('surveys/123'), 'https://api-test.oktave.co/surveys/123');
    }

    public function testSetBaseURLUpdatesAPIEndpoint(): void
    {
        $customURL = 'https://api.yourcompany.com';
        $this->underTest->setBaseURL($customURL);
        $this->assertEquals($this->underTest->getAPIEndpoint(), 'https://api.yourcompany.com/');
    }

}
