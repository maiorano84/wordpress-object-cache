<?php

namespace Maiorano\ObjectCache\Cache;

interface KeyResolverInterface
{
    public const string DEFAULT_KEY_SEPARATOR = ':';

    public function resolve(string $key, GroupInterface $group): CacheKeyInterface;

    public function setPrefix(int|string $prefix): void;
}