<?php

#[AllowDynamicProperties]
class WP_Object_Cache
{
    public $cache_hits = 0;
    public $cache_misses = 0;

    public function add($key, $data, $group = 'default', $expire = 0): bool
    {
        return true;
    }

    public function add_multiple(array $data, $group = '', $expire = 0): array
    {
        return [];
    }

    public function replace($key, $data, $group = 'default', $expire = 0): bool
    {
        return true;
    }

    public function set($key, $data, $group = 'default', $expire = 0): bool
    {
        return true;
    }

    public function set_multiple(array $data, $group = '', $expire = 0): array
    {
        return [];
    }

    public function get($key, $group = 'default', $force = false, &$found = null): mixed
    {
        return '';
    }

    public function get_multiple($keys, $group = 'default', $force = false): array
    {
        return [];
    }

    public function delete($key, $group = 'default', $deprecated = false): bool
    {
        return true;
    }

    public function delete_multiple(array $keys, $group = ''): array
    {
        return [];
    }

    public function incr($key, $offset = 1, $group = 'default'): int|false
    {
        return 0;
    }

    public function decr($key, $offset = 1, $group = 'default'): int|false
    {
        return 0;
    }

    public function flush(): bool
    {
        return true;
    }

    public function flush_group($group): bool
    {
        return true;
    }

    public function add_global_groups($groups): void
    {
    }

    public function switch_to_blog($blog_id): void
    {
    }

    public function stats(): void
    {
    }
}