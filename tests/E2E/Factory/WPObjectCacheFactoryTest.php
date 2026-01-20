<?php

namespace E2E\Factory;

use Maiorano\ObjectCache\Adapter\Psr6;
use Maiorano\ObjectCache\Adapter\WPObjectCache;
use Maiorano\ObjectCache\Cache\BlogKeyResolver;
use Maiorano\ObjectCache\Cache\GroupManager;
use Maiorano\ObjectCache\Factory\WPObjectCacheFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

#[CoversClass(WPObjectCacheFactory::class)]
#[CoversClass(Psr6::class)]
#[CoversClass(WPObjectCache::class)]
#[CoversClass(BlogKeyResolver::class)]
#[CoversClass(GroupManager::class)]
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
        $adapter = WPObjectCacheFactory::createAdapterFromConfig('memcached');
        $this->assertInstanceOf(Psr6::class, $adapter);
    }

    #[DoesNotPerformAssertions]
    public function testCreateAdapterWithoutNamespace(): void
    {
        define('WP_OBJECT_CACHE_DEFAULT_NS_ENABLED', false);
        WPObjectCacheFactory::createAdapterFromConfig('wordpress');
    }
}
