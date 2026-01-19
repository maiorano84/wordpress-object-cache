<?php

namespace Maiorano\ObjectCache;

interface GroupAwareInterface
{
    public function addGroup(array|string $group, string $type = null): void;

    public function flushGroup(string $group): bool;
}