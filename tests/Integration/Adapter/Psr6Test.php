<?php

namespace Integration\Adapter;

use Maiorano\ObjectCache\Adapter\Psr6;
use Maiorano\ObjectCache\BatchOperationInterface;
use Maiorano\ObjectCache\Cache\BlogKeyResolver;
use Maiorano\ObjectCache\Cache\Group;
use Maiorano\ObjectCache\Cache\GroupManager;
use Maiorano\ObjectCache\Cache\GroupManagerInterface;
use Maiorano\ObjectCache\Cache\Key;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

#[CoversClass(Psr6::class)]
#[CoversClass(GroupManager::class)]
#[CoversClass(BlogKeyResolver::class)]
#[CoversClass(Group::class)]
#[CoversClass(Key::class)]
class Psr6Test extends TestCase
{
    private MockObject $cache;
    private GroupManagerInterface $manager;
    private BatchOperationInterface $adapter;

    public function setUp(): void
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->manager = new GroupManager(new BlogKeyResolver);
        $this->adapter = new Psr6($this->cache, $this->manager);
    }

    public function testGetMultiple()
    {
        $item = $this->createMock(CacheItemInterface::class);

        $this->cache
            ->expects($this->exactly(3))
            ->method('getItem')
            ->willReturn($item);

        $valueMatcher = $this->exactly(3);
        $item->method('isHit')->willReturn(true);
        $item
            ->expects($valueMatcher)
            ->method('get')
            ->willReturnCallback(fn() => match ($valueMatcher->numberOfInvocations()) {
                1 => 'value1',
                2 => 'value2',
                3 => 'value3',
            });

        $values = iterator_to_array($this->adapter->getMultiple(['key1', 'key2', 'key3']));

        $this->assertEquals('value1', $values['key1']);
        $this->assertEquals('value2', $values['key2']);
        $this->assertEquals('value3', $values['key3']);
    }

    #[AllowMockObjectsWithoutExpectations]
    public function testSetMultiple()
    {
        $item = $this->createMock(CacheItemInterface::class);
        $this->cache
            ->expects($this->exactly(3))
            ->method('getItem')
            ->willReturn($item);

        $this->cache
            ->expects($this->exactly(3))
            ->method('save')
            ->willReturn(true);

        $values = iterator_to_array($this->adapter->setMultiple([
            'key4' => 'value4',
            'key5' => 'value5',
            'key6' => 'value6',
        ]));

        $this->assertTrue($values['key4']);
        $this->assertTrue($values['key5']);
        $this->assertTrue($values['key6']);
    }

    public function testAddMultiple()
    {
        $this->cache
            ->expects($this->exactly(3))
            ->method('hasItem')
            ->willReturn(true);

        $values = iterator_to_array($this->adapter->addMultiple([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ]));

        $this->assertFalse($values['key1']);
        $this->assertFalse($values['key2']);
        $this->assertFalse($values['key3']);
    }

    public function testDeleteMultiple()
    {
        $this->cache
            ->expects($this->exactly(3))
            ->method('deleteItem')
            ->willReturn(true);

        $values = iterator_to_array($this->adapter->deleteMultiple(['key1', 'key2', 'key3']));

        $this->assertTrue($values['key1']);
        $this->assertTrue($values['key2']);
        $this->assertTrue($values['key3']);
    }
}