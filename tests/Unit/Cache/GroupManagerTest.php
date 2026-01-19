<?php

namespace Maiorano\ObjectCache\Tests\Unit\Cache;

use Maiorano\ObjectCache\Cache\CacheKeyInterface;
use Maiorano\ObjectCache\Cache\GroupInterface;
use Maiorano\ObjectCache\Cache\GroupManager;
use Maiorano\ObjectCache\Cache\GroupManagerInterface;
use Maiorano\ObjectCache\Cache\KeyResolverInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GroupManager::class)]
#[AllowMockObjectsWithoutExpectations]
class GroupManagerTest extends TestCase
{
    private GroupInterface $group;
    private KeyResolverInterface $resolver;
    private GroupManagerInterface $manager;

    public function setUp(): void
    {
        $this->group = $this->createMock(GroupInterface::class);
        $this->resolver = $this->createMock(KeyResolverInterface::class);
        $this->manager = new GroupManager($this->resolver, ['test' => $this->group]);
    }

    public function testGetKey()
    {
        $key = $this->createMock(CacheKeyInterface::class);
        $this->resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->equalTo('key'), $this->group)
            ->willReturn($key);

        $this->assertSame($key, $this->manager->getKey('test', 'key'));
    }

    public function testOffsetUnset()
    {
        unset($this->manager['test']);
        $this->assertCount(0, $this->manager);
    }

    public function testGet()
    {
        $this->assertInstanceOf(GroupInterface::class, $this->manager->get('test'));
    }

    public function testGetIterator()
    {
        $groups = $this->manager->getIterator();
        $this->assertCount(1, iterator_to_array($groups));
    }

    public function testCount()
    {
        $this->assertCount(1, $this->manager);
    }

    public function testOffsetGet()
    {
        $this->assertInstanceOf(GroupInterface::class, $this->manager['test']);
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->manager['test']));
    }

    public function testClear()
    {
        $this->manager->clear();
        $this->assertCount(0, $this->manager);
    }

    public function testGetResolver()
    {
        $this->assertInstanceOf(KeyResolverInterface::class, $this->manager->getResolver());
    }
}
