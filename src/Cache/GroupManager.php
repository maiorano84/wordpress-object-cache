<?php

namespace Maiorano\ObjectCache\Cache;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

final class GroupManager implements GroupManagerInterface, ArrayAccess, IteratorAggregate, Countable
{
    private const string DEFAULT_GROUP_TYPE = Group::class;
    private KeyResolverInterface $keyResolver;
    private array $groups;
    private string $groupType;

    public function __construct(
        KeyResolverInterface $keyResolver,
        array                $groups = [],
        string               $groupType = self::DEFAULT_GROUP_TYPE,
    )
    {
        $this->keyResolver = $keyResolver;
        $this->groups = $groups;
        $this->groupType = $groupType;
    }

    public function getKey(string $group, string $key): CacheKeyInterface
    {
        $g = $this->get($group);
        $k = $this->keyResolver->resolve($key, $g);
        $g->addKey($k);
        return $k;
    }

    public function get(string $name, mixed ...$rest): GroupInterface
    {
        return $this->groups[$name] ?? $this->factory($name, $rest);
    }

    private function factory(string $name, array $rest): GroupInterface
    {
        /**
         * @var GroupInterface $newGroup
         */
        $newGroup = call_user_func_array([$this->groupType, 'create'], [$name, ...$rest]);
        if (isset($this->groups[$newGroup->getName()])) {
            return $this->groups[$newGroup->getName()];
        }

        $this->groups[$newGroup->getName()] = $newGroup;
        return $newGroup;
    }

    public function getIterator(): Traversable
    {
        yield from $this->groups;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->groups[$offset]);
    }

    public function offsetGet(mixed $offset): GroupInterface
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $group = $value;
        if (is_string($value)) {
            $group = $this->get($value);
        }
        if (is_array($value)) {
            $group = $this->get($offset, ...$value);
        }
        $this->groups[$offset] = $group;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->groups[$offset]);
    }

    public function count(): int
    {
        return count($this->groups);
    }

    public function clear(): void
    {
        $this->groups = [];
    }

    public function getResolver(): KeyResolverInterface
    {
        return $this->keyResolver;
    }
}