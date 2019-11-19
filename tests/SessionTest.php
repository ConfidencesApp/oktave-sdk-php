<?php

namespace Oktave\SDK\Tests;

use Oktave;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    /**
     * @var Oktave\Session
     */
    private $underTest;

    public function setUp(): void
    {
        $this->underTest = new Oktave\Session();
    }

    public function testCanSetAndGetKey(): void
    {
        $this->underTest->setKey('test', []);
        $this->assertEquals([], $this->underTest->getKey('test'));
    }

    public function testGetUnsetKeyReturnsFalse(): void
    {
        $this->assertEquals(false, $this->underTest->getKey('nope'));
    }

    public function testRemoveKeyRemovesFromSessionCorrectly(): void
    {
        $this->underTest->setKey('tmp', 'value');
        $this->underTest->removeKey('tmp');
        $this->assertEquals(false, $this->underTest->getKey('tmp'));
    }

}
