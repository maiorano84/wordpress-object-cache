<?php

namespace Maiorano\ObjectCache;

interface ConnectionAwareInterface
{
    public function close(): bool;
}