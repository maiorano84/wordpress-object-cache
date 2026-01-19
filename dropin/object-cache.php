<?php

use Maiorano\ObjectCache\ConnectionAwareInterface;
use Maiorano\ObjectCache\Factory\WPObjectCacheFactory;
use Maiorano\ObjectCache\RuntimeAwareInterface;

defined('ABSPATH') || exit;

require_once WP_CONTENT_DIR . '/plugins/object-cache/vendor/autoload.php';

WPObjectCacheFactory::autoload();

function wp_cache_init()
{
    $GLOBALS['wp_object_cache'] = WPObjectCacheFactory::create();;
}

function wp_cache_add($key, $data, $group = 'default', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->add($key, $data, $group, (int)$expire);
}

function wp_cache_add_multiple(array $data, $group = 'default', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->addMultiple($data, $group, (int)$expire);
}

function wp_cache_replace($key, $data, $group = 'default', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->replace($key, $data, $group, (int)$expire);
}

function wp_cache_set($key, $data, $group = 'default', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->set($key, $data, $group, (int)$expire);
}

function wp_cache_set_multiple(array $data, $group = 'default', $expire = 0)
{
    global $wp_object_cache;

    return $wp_object_cache->setMultiple($data, $group, (int)$expire);
}

function wp_cache_get($key, $group = 'default', $force = false, &$found = null)
{
    global $wp_object_cache;

    return $wp_object_cache->get($key, $group, $force, $found);
}

function wp_cache_get_multiple($keys, $group = 'default', $force = false)
{
    global $wp_object_cache;

    return $wp_object_cache->getMultiple($keys, $group, $force);
}

function wp_cache_delete($key, $group = 'default')
{
    global $wp_object_cache;

    return $wp_object_cache->delete($key, $group);
}

function wp_cache_delete_multiple(array $keys, $group = 'default')
{
    global $wp_object_cache;

    return $wp_object_cache->deleteMultiple($keys, $group);
}

function wp_cache_incr($key, $offset = 1, $group = 'default')
{
    global $wp_object_cache;

    return $wp_object_cache->incr($key, $offset, $group);
}

function wp_cache_decr($key, $offset = 1, $group = 'default')
{
    global $wp_object_cache;

    return $wp_object_cache->decr($key, $offset, $group);
}

function wp_cache_flush()
{
    global $wp_object_cache;

    return $wp_object_cache->flush();
}

function wp_cache_flush_runtime()
{
    global $wp_object_cache;

    if ($wp_object_cache instanceof RuntimeAwareInterface) {
        return $wp_object_cache->flushRuntime();
    }

    return false;
}

function wp_cache_flush_group($group)
{
    global $wp_object_cache;

    $wp_object_cache->flushGroup($group);
}

function wp_cache_supports($feature)
{
    global $wp_object_cache;

    return $wp_object_cache->supports($feature);
}

function wp_cache_close()
{
    global $wp_object_cache;

    if ($wp_object_cache instanceof ConnectionAwareInterface) {
        return $wp_object_cache->close();
    }

    return true;
}

function wp_cache_add_global_groups($groups)
{
    global $wp_object_cache;

    $wp_object_cache->addGroup($groups, 'global');
}

function wp_cache_add_non_persistent_groups($groups)
{
    global $wp_object_cache;

    $wp_object_cache->addGroup($groups, 'non_persistent');
}

function wp_cache_switch_to_blog($blog_id)
{
    global $wp_object_cache;

    $wp_object_cache->setPrefix($blog_id);
}

function wp_cache_reset()
{
    _deprecated_function(__FUNCTION__, '3.5.0', 'wp_cache_switch_to_blog()');

    global $wp_object_cache;

    $wp_object_cache->flush();
}