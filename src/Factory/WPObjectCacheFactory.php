<?php

namespace Maiorano\ObjectCache\Factory;

use Maiorano\ObjectCache\Adapter\Psr6;
use Maiorano\ObjectCache\Adapter\WPObjectCache;
use Maiorano\ObjectCache\Cache\BlogKeyResolver;
use Maiorano\ObjectCache\Cache\GroupManager;
use Maiorano\ObjectCache\Cache\GroupManagerInterface;
use Maiorano\ObjectCache\Cache\KeyResolverInterface;
use Maiorano\ObjectCache\Cache\Psr6Key;
use Maiorano\ObjectCache\WPObjectCacheInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use WP_Object_Cache as WPCache;

final class WPObjectCacheFactory
{
    public static function autoload(): void
    {
        require_once ABSPATH . WPINC . '/class-wp-object-cache.php';
    }

    public static function create(): WPObjectCacheInterface
    {
        $adapterType = self::getConfigOption('WP_OBJECT_CACHE_ADAPTER', 'wordpress');
        return apply_filters('maiorano:object-cache:adapter', self::createAdapterFromConfig($adapterType));
    }

    private static function getConfigOption(string $opt, ?string $default = null): ?string
    {
        return match (true) {
            defined($opt) => constant($opt),
            getenv($opt) !== false => getenv($opt) ?: $default,
            default => $default,
        };
    }

    public static function createAdapterFromConfig(string $adapterType): WPObjectCacheInterface
    {
        $defaultNs = self::loadCacheNamespace();
        $defaultGroupManager = self::loadGroupManager();
        $defaultRuntime = self::loadRuntimeCache();

        return match ($adapterType) {
            'redis' => new Psr6(
                new RedisAdapter(RedisAdapter::createConnection(
                    self::getConfigOption('WP_OBJECT_CACHE_REDIS_DSN', 'redis://localhost:6379')
                ), $defaultNs),
                $defaultGroupManager,
                $defaultRuntime,
            ),
            'memcache', 'memcached' => new Psr6(
                new MemcachedAdapter(MemcachedAdapter::createConnection(
                    self::getConfigOption('WP_OBJECT_CACHE_MEMCACHED_DSN', 'memcached://localhost:11211')
                ), $defaultNs),
                $defaultGroupManager,
                $defaultRuntime,
            ),
            'apcu' => new Psr6(
                new ApcuAdapter($defaultNs),
                $defaultGroupManager,
                $defaultRuntime,
            ),
            default => new WPObjectCache(new WPCache),
        };
    }

    private static function loadGroupManager(): GroupManagerInterface
    {
        $resolver = self::loadKeyResolver();
        return apply_filters('maiorano:object-cache:default_group_manager', new GroupManager($resolver));
    }

    private static function loadKeyResolver(): KeyResolverInterface
    {
        return apply_filters('maiorano:object-cache:default_key_resolver', new BlogKeyResolver(
            is_multisite(),
            get_current_blog_id(),
            '.',
            Psr6Key::class,
        ));
    }

    private static function loadRuntimeCache(): WPObjectCacheInterface
    {
        return apply_filters('maiorano:object-cache:default_runtime', self::loadWordpressCache());
    }

    private static function loadWordpressCache(): WPObjectCacheInterface
    {
        return new WPObjectCache(new WPCache);
    }

    private static function loadCacheNamespace(): string
    {
        if(self::getConfigOption('WP_OBJECT_CACHE_DEFAULT_NS_ENABLED', true)) {
            global $wpdb;
            $defaultNs = self::getConfigOption('WP_OBJECT_CACHE_DEFAULT_NS', $wpdb->prefix);
            return (string)apply_filters('maiorano:object-cache:default_namespace', $defaultNs);
        }
        return '';
    }
}