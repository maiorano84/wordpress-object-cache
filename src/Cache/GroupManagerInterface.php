<?php

namespace Maiorano\ObjectCache\Cache;

interface GroupManagerInterface
{
    public function getResolver(): KeyResolverInterface;

    public function get(string $name): GroupInterface;

    public function getKey(string $group, string $key): CacheKeyInterface;

    public function clear(): void;
}