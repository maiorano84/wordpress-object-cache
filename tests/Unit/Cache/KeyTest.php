<?php

namespace Maiorano\ObjectCache\Tests\Unit\Cache;

use Maiorano\ObjectCache\Cache\CacheKeyInterface;
use Maiorano\ObjectCache\Cache\Key;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Key::class)]
class KeyTest extends TestCase
{
    private CacheKeyInterface $key;

    public function setUp(): void
    {
        $this->key = new Key('test_name', 'test_value');
    }

    public function testGetName()
    {
        $this->assertEquals('test_name', $this->key->getName());
    }

    public function testGetValue()
    {
        $this->assertEquals('test_value', $this->key->getValue());
    }

    public function testToString()
    {
        $this->assertEquals('test_value', (string)$this->key);
    }
}
