<?php

namespace Maiorano\ObjectCache\Tests\Unit\Adapter;

use Maiorano\ObjectCache\Adapter\WPObjectCache;
use Maiorano\ObjectCache\Helper\BackwardsCompatibleNames;
use Maiorano\ObjectCache\Helper\SupportFeatures;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use WP_Object_Cache as WPCache;

#[CoversClass(WPObjectCache::class)]
#[CoversTrait(BackwardsCompatibleNames::class)]
#[CoversTrait(SupportFeatures::class)]
class WPObjectCacheTest extends TestCase
{
    private MockObject $cache;
    private WPObjectCache $adapter;

    public static function featureProvider(): array
    {
        return [
            ['add_multiple', true],
            ['set_multiple', true],
            ['get_multiple', true],
            ['delete_multiple', true],
            ['flush_runtime', true],
            ['flush_group', true],
            ['unknown_feature', false],
        ];
    }

    public function setUp(): void
    {
        $this->cache = $this->createMock(WPCache::class);
        $this->adapter = new WPObjectCache($this->cache);
    }

    public function testAddMultiple()
    {
        $data = ['foo' => 1, 'bar' => 0];
        $this->cache
            ->expects($this->exactly(2))
            ->method('add_multiple')
            ->with($this->equalTo($data), 'test', 0)
            ->willReturn(['foo' => true, 'bar' => false]);

        $result = $this->adapter->addMultiple($data, 'test');
        $this->assertTrue($result['foo']);
        $this->assertFalse($result['bar']);
        $this->assertEquals($result, $this->adapter->add_multiple($data, 'test'));
    }

    public function testReplace()
    {
        $this->cache
            ->expects($this->once())
            ->method('replace')
            ->with($this->equalTo('foo'), $this->equalTo('bar'), 'test', 0)
            ->willReturn(true);

        $this->assertTrue($this->adapter->replace('foo', 'bar', 'test'));
    }

    public function testFlushGroup()
    {
        $this->cache
            ->expects($this->exactly(2))
            ->method('flush_group')
            ->with($this->equalTo('test'))
            ->willReturn(true);

        $this->assertTrue($this->adapter->flushGroup('test'));
        $this->assertTrue($this->adapter->flush_group('test'));
    }

    public function testStats()
    {
        $this->cache
            ->expects($this->once())
            ->method('stats');

        $this->adapter->stats();
    }

    public function testSet()
    {
        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with($this->equalTo('foo'), $this->equalTo('bar'), 'test', 0)
            ->willReturn(true);

        $this->assertTrue($this->adapter->set('foo', 'bar', 'test'));
    }

    public function testDecr()
    {
        $this->cache
            ->expects($this->once())
            ->method('decr')
            ->with($this->equalTo('foo'), $this->equalTo(1), 'test')
            ->willReturn(0);

        $this->assertEquals(0, $this->adapter->decr('foo', 1, 'test'));
    }

    public function testAdd()
    {
        $this->cache
            ->expects($this->once())
            ->method('add')
            ->with($this->equalTo('foo'), $this->equalTo('bar'), 'test', 0)
            ->willReturn(true);

        $this->assertTrue($this->adapter->add('foo', 'bar', 'test'));
    }

    public function testGetMultiple()
    {
        $data = ['foo', 'bar'];
        $this->cache
            ->expects($this->exactly(2))
            ->method('get_multiple')
            ->with($this->equalTo($data), 'test')
            ->willReturn(['foo' => 1, 'bar' => 2]);

        $result = $this->adapter->getMultiple($data, 'test');
        $this->assertEquals(1, $result['foo']);
        $this->assertEquals(2, $result['bar']);
        $this->assertEquals($result, $this->adapter->get_multiple($data, 'test'));
    }

    public function testSetMultiple()
    {
        $data = ['foo' => 1, 'bar' => 0];
        $this->cache
            ->expects($this->exactly(2))
            ->method('set_multiple')
            ->with($this->equalTo($data), 'test', 0)
            ->willReturn(['foo' => true, 'bar' => false]);

        $result = $this->adapter->setMultiple($data, 'test');
        $this->assertTrue($result['foo']);
        $this->assertFalse($result['bar']);
        $this->assertEquals($result, $this->adapter->set_multiple($data, 'test'));
    }

    public function testFlushRuntime()
    {
        $this->cache
            ->expects($this->once())
            ->method('flush')
            ->willReturn(true);

        $this->assertTrue($this->adapter->flushRuntime());
    }

    public function testDeleteMultiple()
    {
        $data = ['foo', 'bar'];
        $this->cache
            ->expects($this->exactly(2))
            ->method('delete_multiple')
            ->with($this->equalTo($data), 'test')
            ->willReturn(['foo' => true, 'bar' => false]);

        $result = $this->adapter->deleteMultiple($data, 'test');
        $this->assertTrue($result['foo']);
        $this->assertFalse($result['bar']);
        $this->assertEquals($result, $this->adapter->delete_multiple($data, 'test'));
    }

    public function testAddGroup()
    {
        $this->cache
            ->expects($this->exactly(2))
            ->method('add_global_groups')
            ->with($this->equalTo('test_group'));

        $this->adapter->addGroup('test_group', 'global');
        $this->adapter->add_global_groups('test_group');
        $this->adapter->add_non_persistent_groups('test_group');
    }

    public function testSetPrefix()
    {
        $this->cache
            ->expects($this->exactly(2))
            ->method('switch_to_blog')
            ->with($this->equalTo(5));

        $this->adapter->setPrefix(5);
        $this->adapter->switch_to_blog(5);
    }

    public function testGet()
    {
        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('foo'), 'test')
            ->willReturn('bar');

        $this->assertEquals('bar', $this->adapter->get('foo', 'test'));
    }

    public function testFlush()
    {
        $this->cache
            ->expects($this->once())
            ->method('flush')
            ->willReturn(true);

        $this->assertTrue($this->adapter->flush());
    }

    public function testDelete()
    {
        $this->cache
            ->expects($this->once())
            ->method('delete')
            ->with($this->equalTo('foo'), 'test')
            ->willReturn(true);

        $this->assertTrue($this->adapter->delete('foo', 'test'));
    }

    public function testIncr()
    {
        $this->cache
            ->expects($this->once())
            ->method('incr')
            ->with($this->equalTo('foo'), $this->equalTo(1), 'test')
            ->willReturn(1);

        $this->assertEquals(1, $this->adapter->incr('foo', 1, 'test'));
    }

    #[DataProvider('featureProvider')]
    #[AllowMockObjectsWithoutExpectations]
    public function testSupports(string $feature, bool $expected)
    {
        $this->assertSame($expected, $this->adapter->supports($feature));
    }
}
