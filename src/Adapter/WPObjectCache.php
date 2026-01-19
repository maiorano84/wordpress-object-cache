<?php

namespace Maiorano\ObjectCache\Adapter;

use Maiorano\ObjectCache\BatchOperationInterface;
use Maiorano\ObjectCache\GroupAwareInterface;
use Maiorano\ObjectCache\Helper\BackwardsCompatibleNames;
use Maiorano\ObjectCache\Helper\SupportFeatures;
use Maiorano\ObjectCache\RuntimeAwareInterface;
use WP_Object_Cache as WPCache;

final class WPObjectCache implements BatchOperationInterface, GroupAwareInterface, RuntimeAwareInterface
{
    use BackwardsCompatibleNames;
    use SupportFeatures;

    private WPCache $cache;

    public function __construct(WPCache $cache)
    {
        $this->cache = $cache;
    }

    public function addMultiple(array $data, string $group = 'default', int $expire = 0): iterable
    {
        return $this->cache->add_multiple($data, $group, $expire);
    }

    public function setMultiple(array $data, string $group = 'default', int $expire = 0): iterable
    {
        return $this->cache->set_multiple($data, $group, $expire);
    }

    public function getMultiple(array $keys, string $group = 'default', bool $force = false): iterable
    {
        return $this->cache->get_multiple($keys, $group, $force);
    }

    public function deleteMultiple(array $keys, string $group = 'default'): iterable
    {
        return $this->cache->delete_multiple($keys, $group);
    }

    public function add(int|string $key, mixed $data, string $group = 'default', int $expire = 0): bool
    {
        return $this->cache->add($key, $data, $group, $expire);
    }

    public function replace(int|string $key, mixed $data, string $group = 'default', int $expire = 0): bool
    {
        return $this->cache->replace($key, $data, $group, $expire);
    }

    public function set(int|string $key, mixed $data, string $group = 'default', int $expire = 0): bool
    {
        return $this->cache->set($key, $data, $group, $expire);
    }

    public function get(int|string $key, string $group = 'default', bool $force = false, ?bool &$found = null): mixed
    {
        return $this->cache->get($key, $group, $force, $found);
    }

    public function delete(int|string $key, string $group = 'default'): bool
    {
        return $this->cache->delete($key, $group);
    }

    public function incr(int|string $key, int $offset = 1, string $group = 'default'): int|false
    {
        return $this->cache->incr($key, $offset, $group);
    }

    public function decr(int|string $key, int $offset = 1, string $group = 'default'): int|false
    {
        return $this->cache->decr($key, $offset, $group);
    }

    public function flushGroup(string $group): bool
    {
        return $this->cache->flush_group($group);
    }

    public function flushRuntime(): bool
    {
        return $this->flush();
    }

    public function flush(): bool
    {
        return $this->cache->flush();
    }

    public function addGroup(array|string $group, string $type = null): void
    {
        match ($type) {
            'global' => $this->cache->add_global_groups($group),
            default => null,
        };
    }

    public function setPrefix(int|string $prefix): void
    {
        $this->cache->switch_to_blog($prefix);
    }

    public function stats(): void
    {
        $this->cache->stats();
    }
}