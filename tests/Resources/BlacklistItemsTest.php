<?php

namespace Oktave\SDK\Tests\Resources;

use Mockery;
use Oktave;
use PHPUnit\Framework\TestCase;

class BlacklistItemsTest extends TestCase
{
    /**
     * @var Oktave\Resources\BlacklistItems
     */
    private $underTest;

    public function setUp(): void
    {
        $client = Mockery::mock(Oktave\Client::class);
        $this->underTest = new Oktave\Resources\BlacklistItems($client);
    }

    public function testGetResourceURI(): void
    {
        $this->assertEquals('api/blacklist-items', $this->underTest->getResourceURI());
    }

}
