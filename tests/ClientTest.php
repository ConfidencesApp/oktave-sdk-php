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
        'client_id'     => 'abc',
        'client_secret' => '123',
        'api_endpoint'  => 'https://api-test.oktave.com',
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

    public function testGetBase(): void
    {
        $this->assertEquals($this->underTest->getBase(), 'https://api-test.oktave.com');
    }

    public function testGetAuthURI(): void
    {
        $this->assertEquals($this->underTest->getAuthURI(), 'api/token');
    }

    public function testGetAuthEndpoint(): void
    {
        $this->assertEquals($this->underTest->getAuthEndpoint(), 'https://api-test.oktave.com/api/token');
    }

    public function testGetAPIEndpoint(): void
    {
        $this->assertEquals($this->underTest->getAPIEndpoint(), 'https://api-test.oktave.com/');
    }

    public function testGetAPIEndpointWithURIReturnsCorrectURL(): void
    {
        $this->assertEquals($this->underTest->getAPIEndpoint('surveys/123'), 'https://api-test.oktave.com/surveys/123');
    }

    public function testSetBaseURLUpdatesAPIEndpoint(): void
    {
        $customURL = 'https://api.yourcompany.com';
        $this->underTest->setBaseURL($customURL);
        $this->assertEquals($this->underTest->getAPIEndpoint(), 'https://api.yourcompany.com/');
    }

}
