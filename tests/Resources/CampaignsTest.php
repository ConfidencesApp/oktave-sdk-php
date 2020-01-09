<?php

namespace Oktave\SDK\Tests\Resources;

use Mockery;
use Oktave;
use PHPUnit\Framework\TestCase;
use stdClass;

class CampaignsTest extends TestCase
{
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|Oktave\Client
     */
    private $client;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|Oktave\Interfaces\Storage
     */
    private $storage;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|Oktave\Request
     */
    private $requestLibrary;

    /**
     * @var Oktave\Resources\BlacklistItems
     */
    private $underTest;

    public function setUp(): void
    {
        $this->client = Mockery::mock(Oktave\Client::class);
        $this->client->shouldReceive('getAPIEndpoint')
            ->andReturn('https://api.oktave.com')
            ->shouldReceive('getAuthEndpoint')
            ->andReturn('https://api.oktave.com/api/token')
            ->shouldReceive('getClientID')
            ->andReturn('123')
            ->shouldReceive('getClientSecret')
            ->andReturn('456')
            ->shouldReceive('getTeam')
            ->andReturn(null);

        $this->storage = Mockery::mock(Oktave\Interfaces\Storage::class);
        $sessonObject = new stdClass();
        $sessonObject->access_token = '7893e06821bfbee0ea82afe2942dab734713cf5a';
        $sessonObject->expires = time() + 600;
        $this->storage->shouldReceive('getKey')
            ->with('authentication')
            ->andReturn($sessonObject);

        $response = Mockery::mock(Oktave\Response::class);

        $this->requestLibrary = Mockery::mock(Oktave\Request::class);
        $this->requestLibrary->shouldReceive('make')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('setURL')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('addHeaders')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('setBody')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('addHeader')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('setMethod')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('getResponse')
            ->andReturn($response)
            ->shouldReceive('setQueryStringParams')
            ->andReturn($this->requestLibrary)
            ->shouldReceive('getRaw')
            ->andReturn(new StdClass);

        $this->underTest = new Oktave\Resources\Campaigns($this->client, $this->requestLibrary, $this->storage);
    }

    public function testGetResourceURI(): void
    {
        $this->assertEquals('api/emitters', $this->underTest->getResourceURI());
    }

    public function testSendWithInvalidRecipient()
    {
        $this->expectException(Oktave\Exceptions\InvalidArgumentException::class);
        $this->underTest->send('123', []);
    }

    public function testSendWithOneSimpleRecipient()
    {
        $this->requestLibrary
            ->shouldReceive('setBody')
            ->with([
                'recipients' => ['email@example.com'],
            ]);

        $sut = new Oktave\Resources\Campaigns($this->client, $this->requestLibrary, $this->storage);
        $sut->send('321', 'email@example.com');

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );
        Mockery::close();
    }

    public function testSendWithMultipleSimpleRecipients()
    {
        $this->requestLibrary
            ->shouldReceive('setBody')
            ->with([
                'recipients' => ['email1@example.com', 'email2@example.com'],
            ]);

        $sut = new Oktave\Resources\Campaigns($this->client, $this->requestLibrary, $this->storage);
        $sut->send('321', ['email1@example.com', 'email2@example.com']);

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );
        Mockery::close();
    }

    public function testSendWithOneComplexRecipient()
    {
        $this->requestLibrary
            ->shouldReceive('setBody')
            ->with([
                'recipients' => [
                    ['email' => 'email@example.com', 'foo' => 'bar'],
                ],
            ]);

        $sut = new Oktave\Resources\Campaigns($this->client, $this->requestLibrary, $this->storage);
        $sut->send('321', ['email' => 'email@example.com', 'foo' => 'bar']);

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );
        Mockery::close();
    }

    public function testSendWithMultipleComplexRecipients()
    {
        $this->requestLibrary
            ->shouldReceive('setBody')
            ->with([
                'recipients' => [
                    ['email' => 'email1@example.com', 'foo' => 'bar'],
                    ['email' => 'email2@example.com', 'foo' => 'baz'],
                ],
            ]);

        $sut = new Oktave\Resources\Campaigns($this->client, $this->requestLibrary, $this->storage);
        $sut->send('321', [
            ['email' => 'email1@example.com', 'foo' => 'bar'],
            ['email' => 'email2@example.com', 'foo' => 'baz'],
        ]);

        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );
        Mockery::close();
    }
}
