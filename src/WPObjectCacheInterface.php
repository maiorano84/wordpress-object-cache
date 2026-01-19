<?php

namespace Maiorano\ObjectCache;

interface WPObjectCacheInterface
{
    public function add(int|string $key, mixed $data, string $group = 'default', int $expire = 0): bool;

    public function replace(int|string $key, mixed $data, string $group = 'default', int $expire = 0): bool;

    public function set(int|string $key, mixed $data, string $group = 'default', int $expire = 0): bool;

    public function get(int|string $key, string $group = 'default', bool $force = false, ?bool &$found = null): mixed;

    public function delete(int|string $key, string $group = 'default'): bool;

    public function incr(int|string $key, int $offset = 1, string $group = 'default'): int|false;

    public function decr(int|string $key, int $offset = 1, string $group = 'default'): int|false;

    public function flush(): bool;

    public function supports(string $feature): bool;

    public function setPrefix(int|string $prefix): void;

    public function stats(): void;
}