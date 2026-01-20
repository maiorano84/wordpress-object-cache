<?php

namespace E2E\Factory;

use Maiorano\ObjectCache\Adapter\Psr6;
use Maiorano\ObjectCache\Adapter\WPObjectCache;
use Maiorano\ObjectCache\Cache\BlogKeyResolver;
use Maiorano\ObjectCache\Cache\Group;
use Maiorano\ObjectCache\Cache\GroupManager;
use Maiorano\ObjectCache\Cache\Key;
use Maiorano\ObjectCache\Cache\Psr6Key;
use Maiorano\ObjectCache\Factory\WPObjectCacheFactory;
use Maiorano\ObjectCache\WPObjectCacheInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

#[CoversClass(WPObjectCacheFactory::class)]
#[CoversClass(Psr6::class)]
#[CoversClass(WPObjectCache::class)]
#[CoversClass(BlogKeyResolver::class)]
#[CoversClass(GroupManager::class)]
#[CoversClass(Group::class)]
#[CoversClass(Key::class)]
#[CoversClass(Psr6Key::class)]
class WPObjectCacheFactoryTest extends TestCase
{
    public function testCreateWordpressAdapterFromConfig(): void
    {
        $adapter = WPObjectCacheFactory::createAdapterFromConfig('wordpress');
        $this->assertInstanceOf(WPObjectCache::class, $adapter);
    }

    public function testCreateRedisAdapterFromConfig(): void
    {
        $adapter = WPObjectCacheFactory::createAdapterFromConfig('redis');
        $this->assertInstanceOf(Psr6::class, $adapter);
    }

    public function testCreateMemcachedAdapterFromConfig(): void
    {
        $adapter = WPObjectCacheFactory::createAdapterFromConfig('memcached');
        $this->assertInstanceOf(Psr6::class, $adapter);
    }

    public function testCreateApcuAdapterFromConfig(): void
    {
        $adapter = WPObjectCacheFactory::createAdapterFromConfig('apcu');
        $this->assertInstanceOf(Psr6::class, $adapter);
    }

    #[DoesNotPerformAssertions]
    public function testCreateAdapterWithoutNamespace(): void
    {
        define('WP_OBJECT_CACHE_DEFAULT_NS_ENABLED', false);
        WPObjectCacheFactory::createAdapterFromConfig('wordpress');
    }

    #[DataProvider('adapterDataProvider')]
    public function testAdapters(WPObjectCacheInterface $adapter): void
    {
        $this->assertFalse($adapter->get('non-existent', 'phpunit'));
        $this->assertTrue($adapter->set('non-existent', 'exists', 'phpunit'));

        $newValue = $adapter->get('non-existent', 'phpunit', false, $isHit);
        $this->assertEquals('exists', $newValue);
        $this->assertTrue($isHit);

        $this->assertTrue($adapter->delete('non-existent', 'phpunit'));
        $this->assertFalse($adapter->get('non-existent', 'phpunit'));
    }

    public static function adapterDataProvider(): array
    {
        return [
            [WPObjectCacheFactory::createAdapterFromConfig('redis')],
            [WPObjectCacheFactory::createAdapterFromConfig('memcached')],
            [WPObjectCacheFactory::createAdapterFromConfig('apcu')],
        ];
    }
}
