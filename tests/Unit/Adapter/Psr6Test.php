<?php


namespace Maiorano\ObjectCache\Tests\Unit\Adapter;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Maiorano\ObjectCache\Adapter\Psr6;
use Maiorano\ObjectCache\Cache\CacheKeyInterface;
use Maiorano\ObjectCache\Cache\GroupInterface;
use Maiorano\ObjectCache\Cache\GroupManagerInterface;
use Maiorano\ObjectCache\Cache\GroupMetadataInterface;
use Maiorano\ObjectCache\Cache\KeyResolverInterface;
use Maiorano\ObjectCache\GroupAwareInterface;
use Maiorano\ObjectCache\WPObjectCacheInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Stringable;

#[CoversClass(Psr6::class)]
#[AllowMockObjectsWithoutExpectations]
class Psr6Test extends TestCase
{
    /**
     * @var MockObject & GroupMetadataInterface & Stringable
     */
    private MockObject $group;
    /**
     * @var MockObject & CacheItemPoolInterface
     */
    private MockObject $cache;
    /**
     * @var MockObject & GroupManagerInterface & ArrayAccess
     */
    private MockObject $manager;
    /**
     * @var MockObject & WPObjectCacheInterface & GroupAwareInterface
     */
    private MockObject $runtime;
    /**
     * @var Psr6
     */
    private Psr6 $adapter;

    public static function readActionDataProvider(): array
    {
        return [
            ['key', 'test', true, true, 'cached_value'],
            ['foo', 'bar', false, false, false],
        ];
    }

    public static function writeActionDataProvider(): array
    {
        return [
            ['foo', 'bar', 'default', 0],
            ['bar', 'baz', 'other', 20],
        ];
    }

    public static function incrementDataProvider(): array
    {
        return [
            [true, 0, 1, 1],
            [true, 5, 3, 8],
            [true, 'foo', 2, 2],
            [true, -5, 1, 0],
            [false, 0, 1, false],
        ];
    }

    public static function decrementDataProvider(): array
    {
        return [
            [true, 3, 1, 2],
            [true, 8, 3, 5],
            [true, 'foo', 2, 0],
            [true, 0, 1, 0],
            [false, 0, 1, false],
        ];
    }

    public function setUp(): void
    {
        $this->group = $this->createMockForIntersectionOfInterfaces([
            GroupMetadataInterface::class,
            Stringable::class,
        ]);
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->manager = $this->createMockForIntersectionOfInterfaces([
            GroupManagerInterface::class,
            ArrayAccess::class,
            IteratorAggregate::class,
        ]);
        $this->runtime = $this->createMockForIntersectionOfInterfaces([
            WPObjectCacheInterface::class,
            GroupAwareInterface::class,
        ]);
        $this->adapter = new Psr6(
            $this->cache,
            $this->manager,
            $this->runtime,
        );
    }

    public function testAddGroup()
    {
        $this->manager
            ->expects($this->once())
            ->method('get')
            ->with('test')
            ->willReturn($this->group);

        $this->group
            ->expects($this->once())
            ->method('hasMetadataValue')
            ->with($this->equalTo('type'), $this->equalTo('global'))
            ->willReturn(false);

        $this->group
            ->expects($this->once())
            ->method('addMetadataValue')
            ->with($this->equalTo('type'), $this->equalTo('global'));

        $this->adapter->addGroup('test', 'global');
    }

    public function testFlushGroup()
    {
        $this->runtime
            ->expects($this->once())
            ->method('flushGroup')
            ->with($this->equalTo('test'));

        $this->manager
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('test'))
            ->willReturn($this->group);

        $this->group
            ->expects($this->once())
            ->method('getKeys')
            ->willReturn(['key1', 'key2', 'key3']);

        $this->cache
            ->expects($this->once())
            ->method('deleteItems')
            ->with(['key1', 'key2', 'key3'])
            ->willReturn(true);

        $this->assertTrue($this->adapter->flushGroup('test'));
    }

    public function testFlushGroupNonExisting()
    {
        $this->runtime
            ->expects($this->once())
            ->method('flushGroup')
            ->with('non-existing');

        $this->manager
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('non-existing'))
            ->willReturn($this->group);

        $this->group
            ->expects($this->once())
            ->method('getKeys')
            ->willReturn([]);

        $this->assertFalse($this->adapter->flushGroup('non-existing'));
    }

    #[DataProvider('readActionDataProvider')]
    public function testGet(
        int|string $key,
        string     $group,
        bool       $force,
        bool       $isHit,
        mixed      $expected,
    )
    {
        $this->readExpectations($key, $group, $force, $isHit, $expected);
        $value = $this->adapter->get($key, $group, $force, $found);
        $this->assertEquals($expected, $value);
        $this->assertSame($isHit, $found);
    }

    private function readExpectations(
        int|string $key,
        string     $group,
        bool       $force,
        bool       $isHit,
        mixed      $returnedValue = null,
    ): void
    {
        $cacheKey = $this->createMockForIntersectionOfInterfaces([
            CacheKeyInterface::class,
            Stringable::class,
        ]);
        $item = $this->createMock(CacheItemInterface::class);

        $this->readIsHitExpectations($key, $group, $isHit, $cacheKey, $item);

        if ($isHit) {
            $item
                ->method('get')
                ->willReturn($returnedValue);
        }

        if ($isHit && $force) {
            $this->runtime
                ->method('set')
                ->with(
                    $this->equalTo($key),
                    $this->equalTo($returnedValue),
                    $this->equalTo($group),
                )->willReturn(true);
        }
    }

    private function readIsHitExpectations(
        int|string $key,
        string     $group,
        bool       $isHit,
        MockObject $cacheKey,
        MockObject $item,
    )
    {
        $this->manager
            ->method('getKey')
            ->with($group, $key)
            ->willReturn($cacheKey);

        $this->cache
            ->method('getItem')
            ->with($cacheKey)
            ->willReturn($item);

        $item
            ->method('isHit')
            ->willReturn($isHit);
    }

    public function testGetFromRuntime()
    {
        $this->runtime
            ->expects($this->once())
            ->method('get')
            ->willReturnCallback(function (int|string $key, string $group, bool $force, ?bool &$found) {
                $found = true;
                return 'runtime_value';
            });

        $value = $this->adapter->get('foo', 'test', false, $found);
        $this->assertEquals('runtime_value', $value);
        $this->assertTrue($found);
    }

    #[DataProvider('writeActionDataProvider')]
    public function testSet(
        string $key,
        mixed  $value,
        string $group,
        int    $expires,
    )
    {
        $this->writeExpectations($key, $value, $group, $expires, true);
        $this->assertTrue($this->adapter->set($key, $value, $group, $expires));
    }

    private function writeExpectations(
        string $key,
        mixed  $value,
        string $group,
        int    $expires,
        bool   $expectedReturn,
        bool   $isNonPersistent = false,
        bool   $isImmediate = true,
    ): void
    {
        $cacheKey = $this->createMockForIntersectionOfInterfaces([
            CacheKeyInterface::class,
            Stringable::class,
        ]);

        $item = $this->createMock(CacheItemInterface::class);

        $this->manager
            ->method('get')
            ->with($this->equalTo($group))
            ->willReturn($this->group);

        $this->runtime
            ->method('set')
            ->with(
                $this->equalTo($key),
                $this->equalTo($value),
                $this->equalTo($group),
                $this->equalTo($expires),
            );

        $this->group
            ->method('hasMetadataValue')
            ->with($this->equalTo('type'), $this->equalTo('non_persistent'))
            ->willReturn($isNonPersistent);

        if (!$isNonPersistent) {
            $this->manager
                ->method('getKey')
                ->with($this->equalTo($group), $this->equalTo($key))
                ->willReturn($cacheKey);

            $item
                ->method('set')
                ->with($this->equalTo($value))
                ->willReturn($item);

            $this->cache
                ->method('getItem')
                ->with($cacheKey)
                ->willReturn($item);

            $this->cache
                ->method($isImmediate ? 'save' : 'saveDeferred')
                ->with($item)
                ->willReturn($expectedReturn);

            if ($expires) {
                $item
                    ->method('expiresAfter')
                    ->with($expires);
            }
        }
    }

    public function testNonpersistentSet()
    {
        $this->writeExpectations(
            'foo',
            'bar',
            'default',
            0,
            false,
            true,
        );

        $this->assertFalse($this->adapter->set('foo', 'bar'));
    }

    public function testDelete()
    {
        $cacheKey = $this->createMockForIntersectionOfInterfaces([
            CacheKeyInterface::class,
            Stringable::class,
        ]);

        $this->manager
            ->expects($this->once())
            ->method('getKey')
            ->with($this->equalTo('default'), $this->equalTo('test'))
            ->willReturn($cacheKey);

        $this->cache
            ->expects($this->once())
            ->method('deleteItem')
            ->with($cacheKey)
            ->willReturn(true);

        $this->assertTrue($this->adapter->delete('test'));
    }

    #[DataProvider('writeActionDataProvider')]
    public function testAddNew(
        string $key,
        mixed  $value,
        string $group,
        int    $expires,
    )
    {
        $this->hasItemExpectations($group, $key, false);
        $this->writeExpectations(
            $key,
            $value,
            $group,
            $expires,
            true,
        );
        $this->assertTrue($this->adapter->add($key, $value, $group, $expires));
    }

    private function hasItemExpectations(string $group, string $key, bool $hasItem): void
    {
        $cacheKey = $this->createMockForIntersectionOfInterfaces([
            CacheKeyInterface::class,
            Stringable::class,
        ]);

        $this->manager
            ->method('getKey')
            ->with($this->equalTo($group), $this->equalTo($key))
            ->willReturn($cacheKey);

        $this->cache
            ->expects($this->once())
            ->method('hasItem')
            ->with($cacheKey)
            ->willReturn($hasItem);
    }

    public function testAddExisting()
    {
        $this->hasItemExpectations('default', 'test', true);
        $this->assertFalse($this->adapter->add('test', 'new'));
    }

    #[DataProvider('writeActionDataProvider')]
    public function testReplace(
        string $key,
        mixed  $value,
        string $group,
        int    $expires,
    )
    {
        $this->hasItemExpectations($group, $key, true);
        $this->writeExpectations(
            $key,
            $value,
            $group,
            $expires,
            true,
        );
        $this->assertTrue($this->adapter->replace($key, $value, $group, $expires));
    }

    public function testReplaceNonExisting()
    {
        $this->hasItemExpectations('default', 'test', false);
        $this->assertFalse($this->adapter->replace('test', 'new'));
    }

    #[DataProvider('incrementDataProvider')]
    public function testIncr(
        bool       $exists,
        int|string $startingValue,
        int        $offset,
        mixed      $expectedValue,
    )
    {
        $this->readExpectations('test', 'default', false, $exists, $startingValue);
        $this->writeExpectations('test', $expectedValue, 'default', 0, $startingValue);
        $value = $this->adapter->incr('test', $offset);
        $this->assertEquals($expectedValue, $value);
    }

    #[DataProvider('decrementDataProvider')]
    public function testDecr(
        bool       $exists,
        int|string $startingValue,
        int        $offset,
        mixed      $expectedValue,
    )
    {
        $this->readExpectations('test', 'default', false, $exists, $startingValue);
        $this->writeExpectations('test', $expectedValue, 'default', 0, $startingValue);
        $value = $this->adapter->decr('test', $offset);
        $this->assertEquals($expectedValue, $value);
    }

    public function testFlush()
    {
        $this->runtime
            ->expects($this->once())
            ->method('flush');

        $this->manager
            ->expects($this->once())
            ->method('clear');

        $this->cache
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true);

        $this->assertTrue($this->adapter->flush());
    }

    public function testSetPrefix()
    {
        $resolver = $this->createMock(KeyResolverInterface::class);
        $this->manager
            ->expects($this->once())
            ->method('getResolver')
            ->willReturn($resolver);

        $resolver
            ->expects($this->once())
            ->method('setPrefix')
            ->with($this->equalTo('test'));

        $this->adapter->setPrefix('test');
    }

    public function testStats()
    {
        $group = $this->createMockForIntersectionOfInterfaces([
            GroupInterface::class,
            ArrayAccess::class,
            IteratorAggregate::class,
            Stringable::class,
        ]);

        $key = $this->createMockForIntersectionOfInterfaces([
            CacheKeyInterface::class,
            ArrayAccess::class,
            IteratorAggregate::class,
            Stringable::class,
        ]);

        $group->method('getName')->willReturn('group');
        $group->method('getIterator')->willReturn(new ArrayIterator([
            'key1' => $key,
            'key2' => $key,
            'key3' => $key,
        ]));

        $key->method('getValue')->willReturn('test.key');

        $this->manager
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator([
                'group' => $group,
            ]));

        $this->runtime
            ->expects($this->once())
            ->method('stats');

        $this->expectOutputRegex('/Group:/');
        $this->adapter->stats();
    }
}
