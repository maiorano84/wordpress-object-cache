<?php

namespace Maiorano\ObjectCache;

interface RuntimeAwareInterface
{
    public function flushRuntime(): bool;
}