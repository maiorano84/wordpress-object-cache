<?php

namespace Maiorano\ObjectCache\Cache;

use Stringable;

readonly class Key implements CacheKeyInterface, Stringable
{
    protected string $name;
    protected ?string $value;

    public function __construct(string $name, string $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function getValue(): string
    {
        return $this->value ?? $this->name;
    }
}