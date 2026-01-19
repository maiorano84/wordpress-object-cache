<?php

namespace Maiorano\ObjectCache\Tests\Unit\Cache;

use Maiorano\ObjectCache\Cache\CacheKeyInterface;
use Maiorano\ObjectCache\Cache\Group;
use Maiorano\ObjectCache\Cache\GroupInterface;
use Maiorano\ObjectCache\Cache\GroupMetadataInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Group::class)]
#[AllowMockObjectsWithoutExpectations]
class GroupTest extends TestCase
{
    private CacheKeyInterface $key;
    private GroupMetadataInterface $group;

    public function setUp(): void
    {
        $this->key = $this->createMock(CacheKeyInterface::class);
        $this->group = new Group('test', [
            'mock' => $this->key,
        ], ['type' => ['unit']]);
    }

    public function testOffsetUnset()
    {
        unset($this->group['mock']);
        $this->assertEmpty($this->group->getKeys());
    }

    public function testGetMetadata()
    {
        $this->assertIsArray($this->group->getMetadata());
    }

    public function testGetName()
    {
        $this->assertEquals('test', $this->group->getName());
    }

    public function testGetKeys()
    {
        $this->assertCount(1, $this->group->getKeys());
    }

    public function testGetKey()
    {
        $this->assertInstanceOf(CacheKeyInterface::class, $this->group->getKey('mock'));
    }

    public function testToString()
    {
        $this->assertEquals('test', (string)$this->group);
    }

    public function testCreate()
    {
        $this->assertInstanceOf(GroupInterface::class, Group::create('test'));
    }

    public function testOffsetGet()
    {
        $this->assertInstanceOf(CacheKeyInterface::class, $this->group['mock']);
    }

    public function testRemoveKey()
    {
        $this->key
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('mock');

        $this->group->removeKey($this->key);
        $this->assertCount(0, $this->group->getKeys());
    }

    public function testGetMetadataValue()
    {
        $this->assertEquals(['unit'], $this->group->getMetadataValue('type'));
    }

    public function testSetMetadataValue()
    {
        $this->group->setMetadataValue('foo', 'bar');
        $this->assertEquals('bar', $this->group->getMetadataValue('foo'));
    }

    public function testSetKeys()
    {
        $this->group->setKeys([]);
        $this->assertCount(0, $this->group->getKeys());
    }

    public function testHasMetadataValue()
    {
        $this->assertTrue($this->group->hasMetadataValue('type', 'unit'));
        $this->assertFalse($this->group->hasMetadataValue('type', 'integration'));
        $this->assertFalse($this->group->hasMetadataValue('foo', 'bar'));
    }

    public function testHasKey()
    {
        $this->key
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('mock');

        $this->assertTrue($this->group->hasKey($this->key));
    }

    public function testHasMetadata()
    {
        $this->assertTrue($this->group->hasMetadata('type'));
    }

    public function testGetIterator()
    {
        $keys = $this->group->getIterator();
        $this->assertCount(1, iterator_to_array($keys));
    }

    public function testAddKey()
    {
        $test = $this->createMock(CacheKeyInterface::class);
        $test
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('another');

        $this->group->addKey($test);
        $this->assertCount(2, $this->group->getKeys());
    }

    public function testSetMetadata()
    {
        $this->group->setMetadata(['foo' => 'bar']);
        $this->assertEquals('bar', $this->group->getMetadataValue('foo'));
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->group['mock']));
    }

    public function testCount()
    {
        $this->assertCount(1, $this->group);
    }

    public function testAddMetadataValue()
    {
        $this->group->addMetadataValue('foo', 'bar');
        $this->group->addMetadataValue('type', 'test');
        $this->assertEquals('bar', $this->group->getMetadataValue('foo'));
        $this->assertCount(2, $this->group->getMetadataValue('type'));
        $this->assertTrue($this->group->hasMetadataValue('type', 'unit'));
        $this->assertTrue($this->group->hasMetadataValue('type', 'test'));
    }
}
