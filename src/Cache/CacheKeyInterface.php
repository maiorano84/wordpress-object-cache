<?php

namespace Maiorano\ObjectCache\Cache;

interface CacheKeyInterface
{
    public function getName(): string;

    public function getValue(): string;
}