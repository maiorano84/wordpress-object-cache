<?php

namespace Maiorano\ObjectCache\Adapter;

use Maiorano\ObjectCache\BatchOperationInterface;
use Maiorano\ObjectCache\Cache\GroupManagerInterface;
use Maiorano\ObjectCache\GroupAwareInterface;
use Maiorano\ObjectCache\Helper\SupportFeatures;
use Maiorano\ObjectCache\WPObjectCacheInterface;
use Psr\Cache\CacheItemPoolInterface;

final class Psr6 implements BatchOperationInterface, GroupAwareInterface
{
    use SupportFeatures;

    private CacheItemPoolInterface $cache;
    private GroupManagerInterface $groupManager;
    private ?WPObjectCacheInterface $runtime;

    private int $hits = 0;
    private int $misses = 0;

    public function __construct(
        CacheItemPoolInterface  $cache,
        GroupManagerInterface   $groupManager,
        ?WPObjectCacheInterface $runtime = null,
    )
    {
        $this->cache = $cache;
        $this->groupManager = $groupManager;
        $this->runtime = $runtime;
    }

    public function replace(int|string $key, mixed $data, string $group = 'default', int $expire = 0): bool
    {
        if ($this->canReplace($key, $group)) {
            return $this->write($key, $data, $group, $expire);
        }
        return false;
    }

    private function canReplace(int|string $key, string $group = 'default'): bool
    {
        $k = $this->groupManager->getKey($group, $key);
        return $this->cache->hasItem((string)$k);
    }

    private function write(
        int|string $key,
        mixed      $data,
        string     $group = 'default',
        int        $expire = 0,
        bool       $immediate = true,
    ): bool
    {
        $g = $this->groupManager->get($group);

        if ($this->runtime) {
            $this->runtime->set($key, $data, $group, $expire);
        }

        if (!$g->hasMetadataValue('type', 'non_persistent')) {
            $k = $this->groupManager->getKey($group, $key);
            $item = $this->cache->getItem((string)$k)->set($data);
            if ($expire) {
                $item->expiresAfter($expire);
            }
            return $immediate ? $this->cache->save($item) : $this->cache->saveDeferred($item);
        }
        return false;
    }

    public function get(int|string $key, string $group = 'default', bool $force = false, ?bool &$found = null): mixed
    {
        return $this->read($key, $group, $force, $found);
    }

    private function read(int|string $key, string $group = 'default', bool $force = false, ?bool &$found = null): mixed
    {
        // Get from Runtime if available
        if (!$force && $this->runtime) {
            $value = $this->runtime->get($key, $group, false, $found);
            if ($found) {
                $this->hits++;
                return $value;
            }
        }

        $k = $this->groupManager->getKey($group, $key);
        $item = $this->cache->getItem((string)$k);

        if (!$item->isHit()) {
            $found = false;
            $this->misses++;
            return false;
        }

        $found = true;
        $value = $item->get();
        $this->groupManager->get($group)->addKey($k);
        if ($force && $this->runtime) {
            $this->runtime->set($key, $value, $group);
        }
        $this->hits++;
        return $value;
    }

    public function set(int|string $key, mixed $data, string $group = 'default', int $expire = 0): bool
    {
        return $this->write($key, $data, $group, $expire);
    }

    public function incr(int|string $key, int $offset = 1, string $group = 'default'): int|false
    {
        if (($value = $this->getNumericValue($key, $group)) !== false) {
            $v = max($value + $offset, 0);
            $this->write($key, $v, $group);
            return $v;
        }
        return false;
    }

    private function getNumericValue(int|string $key, string $group): int|false
    {
        $v = $this->read($key, $group, false, $found);
        if (!$found) {
            return false;
        }

        if (!is_numeric($v)) {
            $v = 0;
        }
        return $v;
    }

    public function decr(int|string $key, int $offset = 1, string $group = 'default'): int|false
    {
        if ($value = $this->getNumericValue($key, $group)) {
            $v = max($value - $offset, 0);
            $this->write($key, $v, $group);
            return $v;
        }
        return false;
    }

    public function getMultiple(array $keys, string $group = 'default', bool $force = false): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $group, $force);
        }
    }

    public function setMultiple(array $data, string $group = 'default', int $expire = 0): iterable
    {
        foreach ($data as $key => $value) {
            yield $key => $this->set($key, $data, $group, $expire, false);
        }
        $this->cache->commit();
    }

    public function addMultiple(array $data, string $group = 'default', int $expire = 0): iterable
    {
        foreach ($data as $key => $value) {
            yield $key => $this->add($key, $data, $group, $expire, false);
        }
        $this->cache->commit();
    }

    public function add(int|string $key, mixed $data, string $group = 'default', int $expire = 0): bool
    {
        if ($this->canAdd($key, $group)) {
            return $this->write($key, $data, $group, $expire);
        }
        return false;
    }

    private function canAdd(string $key, string $group = 'default'): bool
    {
        $k = $this->groupManager->getKey($group, $key);
        return !($this->cache->hasItem((string)$k) || wp_suspend_cache_addition());
    }

    public function addGroup(array|string $group, string $type = null): void
    {
        foreach ((array)$group as $item) {
            $g = $this->groupManager->get($item);
            if ($type && !$g->hasMetadataValue('type', $type)) {
                $g->addMetadataValue('type', $type);
            }
        }
    }

    public function flushGroup(string $group): bool
    {
        if ($this->runtime instanceof GroupAwareInterface) {
            $this->runtime->flushGroup($group);
        }

        if ($keys = $this->groupManager->get($group)->getKeys()) {
            return $this->cache->deleteItems($keys);
        }
        return false;
    }

    public function deleteMultiple(array $keys, string $group = 'default'): iterable
    {
        foreach ($keys as $k) {
            yield $k => $this->delete($k, $group);
        }
    }

    public function delete(int|string $key, string $group = 'default'): bool
    {
        $k = $this->groupManager->getKey($group, $key);
        return $this->cache->deleteItem((string)$k);
    }

    public function flush(): bool
    {
        if ($this->runtime) {
            $this->runtime->flush();
        }

        $this->groupManager->clear();
        return $this->cache->clear();
    }

    public function setPrefix(int|string $prefix): void
    {
        $this->groupManager->getResolver()->setPrefix($prefix);
    }

    public function stats(): void
    {
        echo '<p>';
        echo "<strong>Cache Hits:</strong> {$this->hits}<br>";
        echo "<strong>Cache Misses:</strong> {$this->misses}<br>";
        echo '</p>';
        echo '<ul>';
        foreach ($this->groupManager as $group) {
            echo "<li><strong>Group:</strong> $group </li>";
            echo '<ul>';
            foreach ($group as $k) {
                echo "<li><strong>Key:</strong> $k</li>";
            }
            echo '</ul>';
        }
        echo '</ul>';

        if ($this->runtime) {
            echo '<br><strong>Runtime Stats:</strong><br><br>';
            $this->runtime->stats();
        }
    }
}