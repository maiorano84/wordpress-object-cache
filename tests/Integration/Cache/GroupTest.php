<?php

namespace Integration\Cache;

use Maiorano\ObjectCache\Cache\CacheKeyInterface;
use Maiorano\ObjectCache\Cache\Group;
use Maiorano\ObjectCache\Cache\GroupInterface;
use Maiorano\ObjectCache\Cache\Key;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Group::class)]
#[CoversClass(Key::class)]
class GroupTest extends TestCase
{
    private GroupInterface $group;

    public function setUp(): void
    {
        $this->group = new Group('test');
    }

    public function testOffsetSetString()
    {
        $this->group['key'] = 'testValue';

        $this->assertInstanceOf(CacheKeyInterface::class, $this->group['key']);
        $this->assertEquals('testValue', $this->group->getKey('key')->getName());
        $this->assertEquals('testValue', $this->group->getKey('key')->getValue());
    }

    public function testOffsetSetArray()
    {
        $this->group['key'] = ['testName', 'testValue'];

        $this->assertInstanceOf(CacheKeyInterface::class, $this->group['key']);
        $this->assertEquals('testName', $this->group->getKey('key')->getName());
        $this->assertEquals('testValue', $this->group->getKey('key')->getValue());
    }

    public function testOffsetSetKey()
    {
        $this->group['key'] = new Key('testName', 'testValue');

        $this->assertInstanceOf(CacheKeyInterface::class, $this->group['key']);
        $this->assertEquals('testName', $this->group->getKey('key')->getName());
        $this->assertEquals('testValue', $this->group->getKey('key')->getValue());
    }
}