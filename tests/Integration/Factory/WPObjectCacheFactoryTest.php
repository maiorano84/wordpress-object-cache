<?php

namespace Integration\Factory;

use Maiorano\ObjectCache\Adapter\Psr6;
use Maiorano\ObjectCache\Adapter\WPObjectCache;
use Maiorano\ObjectCache\Cache\BlogKeyResolver;
use Maiorano\ObjectCache\Cache\GroupManager;
use Maiorano\ObjectCache\Factory\WPObjectCacheFactory;
use Maiorano\ObjectCache\WPObjectCacheInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

#[CoversClass(WPObjectCacheFactory::class)]
#[CoversClass(WPObjectCache::class)]
#[CoversClass(Psr6::class)]
#[CoversClass(BlogKeyResolver::class)]
#[CoversClass(GroupManager::class)]
class WPObjectCacheFactoryTest extends TestCase
{
    private WPObjectCacheFactory $factory;

    public function setUp(): void
    {
        $this->factory = new WPObjectCacheFactory();
    }

    public function testCreate()
    {
        $this->assertInstanceOf(WPObjectCacheInterface::class, $this->factory->create());
    }

    #[DoesNotPerformAssertions]
    public function testAutoload()
    {
        WPObjectCacheFactory::autoload();
    }

    public function testCreateAdapterFromConfig()
    {
        $this->assertInstanceOf(WPObjectCacheInterface::class, WPObjectCacheFactory::createAdapterFromConfig('wordpress'));
    }
}
