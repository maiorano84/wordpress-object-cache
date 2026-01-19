<?php

namespace Maiorano\ObjectCache\Helper;

use Maiorano\ObjectCache\BatchOperationInterface;
use Maiorano\ObjectCache\GroupAwareInterface;
use Maiorano\ObjectCache\RuntimeAwareInterface;

trait SupportFeatures
{
    public function supports(string $feature): bool
    {
        return match ($feature) {
            'add_multiple',
            'set_multiple',
            'get_multiple',
            'delete_multiple' => $this instanceof BatchOperationInterface,
            'flush_runtime' => $this instanceof RuntimeAwareInterface,
            'flush_group' => $this instanceof GroupAwareInterface,
            default => false,
        };
    }
}