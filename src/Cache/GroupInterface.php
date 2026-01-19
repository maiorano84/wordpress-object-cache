<?php

namespace Maiorano\ObjectCache\Cache;

interface GroupInterface
{
    public const string DEFAULT_GROUP_NAME = 'default';

    public function getName(): string;

    public function getKeys(): array;

    public function getKey(string $key): CacheKeyInterface;

    public function addKey(CacheKeyInterface $key): void;

    public function removeKey(CacheKeyInterface $key): void;

    public function setKeys(array $keys): void;

    public function hasKey(CacheKeyInterface $key): bool;
}