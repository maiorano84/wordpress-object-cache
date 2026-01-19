<?php

namespace Maiorano\ObjectCache;

interface BatchOperationInterface extends WPObjectCacheInterface
{
    public function addMultiple(array $data, string $group = 'default', int $expire = 0): iterable;

    public function setMultiple(array $data, string $group = 'default', int $expire = 0): iterable;

    public function getMultiple(array $keys, string $group = 'default', bool $force = false): iterable;

    public function deleteMultiple(array $keys, string $group = 'default'): iterable;
}