<?php

namespace Oktave\SDK\Tests;

use Mockery;
use Oktave;
use PHPUnit\Framework\TestCase;
use stdClass;

class ResourceTest extends TestCase
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
     * @var Oktave\Resources\Campaigns
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
            ->shouldReceive('getTeamId')
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

    public function testSortMethodUpdatesSort(): void
    {
        $this->underTest->sort('-name');
        $this->assertEquals($this->underTest->getSort(), '-name');
    }

    public function testSortAsFalseMethodUpdatesSort(): void
    {
        $this->underTest->sort('-name');
        $this->underTest->sort(null);
        $this->assertEquals($this->underTest->getSort(), null);
    }

    public function testInvalidPerPageValueThrowException(): void
    {
        $this->expectException(Oktave\Exceptions\InvalidArgumentException::class);
        $this->expectErrorMessage('PerPage value must be in 10, 20, 50, 100');
        $this->underTest->perPage(5);
        $this->assertEquals($this->underTest->getPerPage(), 0);
    }

    public function testPerPageMethodUpdatesPerPage(): void
    {
        $this->underTest->perPage(20);
        $this->assertEquals($this->underTest->getPerPage(), 20);
    }

    public function testOffsetMethodUpdatesOffset(): void
    {
        $this->underTest->page(20);
        $this->assertEquals($this->underTest->getPage(), 20);
    }

    public function testOffsetMethodToFalseUpdatesOffset(): void
    {
        $this->underTest->page(0);
        $this->assertEquals($this->underTest->getPage(), 0);
    }

    public function testGetStorageReturnsStorage(): void
    {
        $this->assertInstanceof(Oktave\Interfaces\Storage::class, $this->underTest->getStorage());
    }

    public function testGetClientReturnsClient(): void
    {
        $this->assertInstanceof(Oktave\Client::class, $this->underTest->getClient());
    }

    public function testCanMakeListRequest(): void
    {
        $this->assertInstanceof(Oktave\Response::class, $this->underTest->all());
    }

    public function testCanMakeGetByIDRequest(): void
    {
        $id = 'c9b96b2f-574d-43f7-be53-3737959ddbb1';
        $this->assertInstanceof(Oktave\Response::class, $this->underTest->get($id));
    }

    public function testCanMakeDeleteRequest(): void
    {
        $id = 'c9b96b2f-574d-43f7-be53-3737959ddbb1';
        $this->assertInstanceof(Oktave\Response::class, $this->underTest->delete($id));
    }

    public function testCanMakeUpdateRequest(): void
    {
        $id = 'c9b96b2f-574d-43f7-be53-3737959ddbb1';
        $this->assertInstanceof(Oktave\Response::class, $this->underTest->update($id, []));
    }

    public function testCanMakeCreateRequest(): void
    {
        $this->assertInstanceof(Oktave\Response::class, $this->underTest->create([]));
    }

    public function testCanMakeRequestForSpecificTeam(): void
    {
        $id = 'c9b96b2f-574d-43f7-be53-3737959ddbb1';
        $team_id = '44395255-6d04-4187-a07f-706fa8a9c901';

        $this->client->shouldReceive('getTeamId')
            ->andReturn($team_id);

        $this->requestLibrary->shouldReceive('addHeader')
            ->with('Oktave-Use-Team-Id', $team_id);

        $this->assertInstanceof(Oktave\Response::class, $this->underTest->call(
            'GET',
            [],
            $id,
            [],
            true,
            false
        ));
    }

    public function testGetAccessTokenMakesAuthenticationCall(): void
    {
        $atResponse = new stdClass;
        $atResponse->access_token = 'ef6206afa0a8a95d342c10b9eadb3082e19c8021';
        $atResponse->expires_in = 600;
        $response = Mockery::mock(Oktave\Response::class);
        $response->shouldReceive('getRaw')
            ->andReturn($atResponse);

        $this->storage = Mockery::mock(Oktave\Interfaces\Storage::class);
        $this->storage->shouldReceive('getKey')
            ->with('authentication')
            ->andReturn(null)
            ->shouldReceive('setKey');

        $requestLibrary = Mockery::mock(Oktave\Request::class);
        $requestLibrary->shouldReceive('make')
            ->andReturn($requestLibrary)
            ->shouldReceive('setURL')
            ->andReturn($requestLibrary)
            ->shouldReceive('addHeaders')
            ->andReturn($requestLibrary)
            ->shouldReceive('setBody')
            ->andReturn($requestLibrary)
            ->shouldReceive('addHeader')
            ->andReturn($requestLibrary)
            ->shouldReceive('setMethod')
            ->andReturn($requestLibrary)
            ->shouldReceive('getResponse')
            ->andReturn($response)
            ->shouldReceive('getRaw')
            ->andReturn(new StdClass);

        $test = new Oktave\Resources\Campaigns($this->client, $requestLibrary, $this->storage);

        $this->assertEquals('ef6206afa0a8a95d342c10b9eadb3082e19c8021', $test->getAccessToken());
    }

    public function testGetAccessTokenWhichIsForbiddenThrowsException(): void
    {
        $this->expectException(Oktave\Exceptions\AuthenticationException::class);
        $response = Mockery::mock(Oktave\Response::class);
        $response->shouldReceive('getRaw')
            ->andReturn(null);

        $this->storage = Mockery::mock(Oktave\Interfaces\Storage::class);
        $this->storage->shouldReceive('getKey')
            ->with('authentication')
            ->andReturn(null)
            ->shouldReceive('setKey');

        $requestLibrary = Mockery::mock(Oktave\Request::class);
        $requestLibrary->shouldReceive('make')
            ->andReturn($requestLibrary)
            ->shouldReceive('setURL')
            ->andReturn($requestLibrary)
            ->shouldReceive('addHeaders')
            ->andReturn($requestLibrary)
            ->shouldReceive('setBody')
            ->andReturn($requestLibrary)
            ->shouldReceive('addHeader')
            ->andReturn($requestLibrary)
            ->shouldReceive('setMethod')
            ->andReturn($requestLibrary)
            ->shouldReceive('getResponse')
            ->andReturn($response)
            ->shouldReceive('getRaw')
            ->andReturn(new StdClass);

        $test = new Oktave\Resources\Campaigns($this->client, $requestLibrary, $this->storage);
        $test->makeAuthenticationCall();
    }

    public function testBuildQueryStringParams(): void
    {
        $this->underTest->with(['categories'])->perPage(10)->page(3)->sort('name');
        $this->assertEquals(
            ['per_page' => 10, 'page' => 3, 'sort' => 'name'],
            $this->underTest->buildQueryStringParams()
        );
    }
}
