<?php

namespace Oktave\SDK\Tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\MultipartStream;
use Mockery;
use Oktave;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class RequestTest extends TestCase
{
    /**
     * @var Oktave\Request
     */
    private $underTest;

    public function setUp(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $result = Mockery::mock(ResponseInterface::class);
        $result->shouldReceive('getStatusCode')
            ->andReturn(200)
            ->shouldReceive('getHeader')
            ->with('X-Oktave-Request-Id')
            ->andReturn('abc-123')
            ->shouldReceive('getBody')
            ->andReturn('{"data":{"responded": true}}');
        $client->shouldReceive('request')
            ->andReturn($result);
        $this->underTest = new Oktave\Request($client);
    }

    public function testCanGetAndSetMethod(): void
    {
        $this->underTest->setMethod('pUt');
        $this->assertEquals('PUT', $this->underTest->getMethod());
    }

    public function testSetMethodToInvalidValueThrowsException(): void
    {
        $this->expectException(Oktave\Exceptions\InvalidRequestMethod::class);
        $this->underTest->setMethod('replace');
    }

    public function testCanGetHeaders(): void
    {
        $expected = [];
        $this->assertEquals([], $this->underTest->getHeaders());
    }

    public function testCanAddASingleHeader(): void
    {
        $this->assertEquals(['MY-HEADER' => 'MY-VALUE'],
            $this->underTest->addHeader('MY-HEADER', 'MY-VALUE')->getHeaders());
    }

    public function testCanAddMultipleHeaders(): void
    {
        $this->underTest->addHeaders(['MY-FIRST-HEADER' => 'MY-FIRST-VALUE', 'MY-SECOND-HEADER' => 'MY-SECOND-VALUE']);
        $this->assertEquals(['MY-FIRST-HEADER' => 'MY-FIRST-VALUE', 'MY-SECOND-HEADER' => 'MY-SECOND-VALUE'],
            $this->underTest->getHeaders());
    }

    public function testCanClearHeaders(): void
    {
        $this->underTest->addHeader('MY-HEADER', 'MY-VALUE')->getHeaders();
        $this->underTest->clearHeaders();
        $this->assertEquals([], $this->underTest->getHeaders());
    }

    public function testCanGetAndSetBody(): void
    {
        $this->underTest->setBody(['my-body-data']);
        $this->assertEquals(['my-body-data'], $this->underTest->getBody());
    }

    public function testCanGetAndSetURL(): void
    {
        $this->underTest->setURL('https://mydomain.com');
        $this->assertEquals('https://mydomain.com', $this->underTest->getURL());
    }

    public function testBodyKeyCanReturnJSON(): void
    {
        $this->underTest->addHeader('Content-Type', 'application/json');
        $this->assertEquals('json', $this->underTest->getBodyKey());
    }

    public function testBodyKeyCanReturnFormParams(): void
    {
        $this->underTest->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->assertEquals('form_params', $this->underTest->getBodyKey());
    }

    public function testBodyKeyCanReturnMultipart(): void
    {
        $this->underTest->addHeader('Content-Type', 'multipart/form-data');
        $this->assertEquals('body', $this->underTest->getBodyKey());
    }

    public function testMultipartPayloadReturnsArray(): void
    {
        $payload = [
            'body' => [
                [
                    'name'     => 'field',
                    'contents' => 'value',
                ],
            ],
        ];

        $payload = $this->underTest->prepareMultipartPayload($payload);

        $this->assertStringContainsString('multipart/form-data; boundary=', $payload['headers']['Content-Type']);
        $this->assertInstanceOf(MultipartStream::class, $payload['body']);
    }

    public function testgetPayloiadCallsMultipart(): void
    {
        $this->underTest->addHeader('Content-Type', 'multipart/form-data');
        $this->underTest->setBody([
            [
                'name'     => 'field',
                'contents' => 'value',
            ],
        ]);

        $payload = $this->underTest->getPayload();
        $this->assertStringContainsString('multipart/form-data; boundary=', $payload['headers']['Content-Type']);
        $this->assertInstanceOf(MultipartStream::class, $payload['body']);
    }

    public function testBodyKeyThrowsExceptionWithInvalidContentType(): void
    {
        $this->expectException(Oktave\Exceptions\InvalidContentType::class);
        $this->underTest->addHeader('Content-Type', 'nope/not');
        $this->underTest->getBodyKey();
    }

    public function testBodyKeyThrowsExceptionWithNoContentType(): void
    {
        $this->expectException(Oktave\Exceptions\InvalidContentType::class);
        $this->underTest->getBodyKey();
    }

    public function testCanGetAndSetQueryParams(): void
    {
        $this->underTest->setQueryStringParams(['page' => ['limit' => 10]]);
        $this->assertEquals(['page' => ['limit' => 10]], $this->underTest->getQueryStringParams());
    }

    public function testCanGetPayload(): void
    {
        $expects = [
            'json'    => [
                'data' => [],
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'My-Header'    => 'my-value',
            ],
            'query'   => [
                'page' => [
                    'limit' => 10,
                ],
            ],
        ];

        $this->underTest->setQueryStringParams(['page' => ['limit' => 10]]);
        $this->underTest->setBody(['data' => []]);
        $this->underTest->addHeader('Content-Type', 'application/json');
        $this->underTest->addHeader('My-Header', 'my-value');

        $this->assertEquals($expects, $this->underTest->getPayload());
    }

    public function testMakeReturnsResponse(): void
    {
        $this->underTest->setURL('https://api-test.oktave.co/surveys');
        $this->assertInstanceOf(Oktave\Response::class, $this->underTest->make()->getResponse());
    }

}
