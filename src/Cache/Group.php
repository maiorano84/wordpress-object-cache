<?php

namespace Maiorano\ObjectCache\Cache;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Stringable;
use Traversable;

final class Group implements GroupMetadataInterface, ArrayAccess, IteratorAggregate, Countable, Stringable
{
    private readonly string $name;
    private array $keys;
    private array $meta;

    public function __construct(string $name, array $keys = [], array $meta = [])
    {
        $this->name = $name;
        $this->keys = $keys;
        $this->meta = $meta;
    }

    public static function create(string $name, mixed ...$rest): GroupInterface
    {
        return new self($name, ...$rest);
    }

    public function getKeys(): array
    {
        return $this->keys;
    }

    public function setKeys(array $keys): void
    {
        $this->keys = $keys;
    }

    public function getKey(string $key): CacheKeyInterface
    {
        return $this->keys[$key];
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return trim($this->name) ?: GroupInterface::DEFAULT_GROUP_NAME;
    }

    public function getIterator(): Traversable
    {
        yield from $this->keys;
    }

    public function addKey(CacheKeyInterface $key): void
    {
        $this->keys[$key->getValue()] = $key;
    }

    public function removeKey(CacheKeyInterface $key): void
    {
        unset($this->keys[$key->getValue()]);
    }

    public function hasKey(CacheKeyInterface $key): bool
    {
        return isset($this->keys[$key->getValue()]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->keys[$offset]);
    }

    public function offsetGet(mixed $offset): CacheKeyInterface
    {
        return $this->keys[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $key = $value;
        if (is_string($value)) {
            $key = new Key($value);
        }
        if (is_array($value)) {
            $key = new Key(...$value);
        }
        $this->keys[$offset] = $key;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->keys[$offset]);
    }

    public function getMetadata(): array
    {
        return $this->meta;
    }

    public function setMetadata(array $data): void
    {
        $this->meta = $data;
    }

    public function hasMetadata(string $key): bool
    {
        return isset($this->meta[$key]);
    }

    public function addMetadataValue(string $key, mixed $data): void
    {
        if (!isset($this->meta[$key])) {
            $this->setMetadataValue($key, $data);
            return;
        }

        $this->meta[$key] = array_merge((array)$this->getMetadataValue($key), (array)$data);
    }

    public function setMetadataValue(string $key, mixed $data): void
    {
        $this->meta[$key] = $data;
    }

    public function getMetadataValue(string $key): mixed
    {
        return $this->meta[$key] ?? null;
    }

    public function hasMetadataValue(string $key, mixed $value): bool
    {
        if ($data = $this->getMetadataValue($key)) {
            return is_array($data)
                ? in_array($value, $data, true)
                : $data === $value;
        }
        return false;
    }

    public function count(): int
    {
        return count($this->keys);
    }
}