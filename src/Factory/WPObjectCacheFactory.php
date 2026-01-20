<?php

namespace Maiorano\ObjectCache\Factory;

use Maiorano\ObjectCache\Adapter\Psr6;
use Maiorano\ObjectCache\Adapter\WPObjectCache;
use Maiorano\ObjectCache\Cache\BlogKeyResolver;
use Maiorano\ObjectCache\Cache\GroupManager;
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
            defined($opt) => constant($opt) ?: $default,
            getenv($opt) !== false => getenv($opt) ?: $default,
            default => $default,
        };
    }

    public static function createAdapterFromConfig(string $adapterType): WPObjectCacheInterface
    {
        global $wpdb;
        $defaultNs = self::getConfigOption('WP_OBJECT_CACHE_DEFAULT_NS', $wpdb->prefix);

        return match ($adapterType) {
            'redis' => new Psr6(
                new RedisAdapter(RedisAdapter::createConnection(
                    self::getConfigOption('WP_OBJECT_CACHE_REDIS_DSN', 'redis://localhost:6379')
                ), $defaultNs),
                new GroupManager(new BlogKeyResolver(
                    is_multisite(),
                    get_current_blog_id(),
                    '.',
                    Psr6Key::class,
                )),
                new WPObjectCache(new WPCache),
            ),
            'memcache', 'memcached' => new Psr6(
                new MemcachedAdapter(MemcachedAdapter::createConnection(
                    self::getConfigOption('WP_OBJECT_CACHE_MEMCACHED_DSN', 'memcached://localhost:11211')
                ), $defaultNs),
                new GroupManager(new BlogKeyResolver(
                    is_multisite(),
                    get_current_blog_id(),
                    '.',
                    Psr6Key::class,
                )),
                new WPObjectCache(new WPCache),
            ),
            'apcu' => new Psr6(
                new ApcuAdapter($defaultNs),
                new GroupManager(new BlogKeyResolver(
                    is_multisite(),
                    get_current_blog_id(),
                    '.',
                    Psr6Key::class,
                )),
                new WPObjectCache(new WPCache),
            ),
            default => new WPObjectCache(new WPCache),
        };
    }
}