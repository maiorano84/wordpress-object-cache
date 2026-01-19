<?php

namespace Maiorano\ObjectCache\Cache;

final readonly class Psr6Key extends Key
{
    private const string PSR_6_RESERVED_CHARACTERS = '{}()\/\\\@:';

    public function getValue(): string
    {
        $pattern = sprintf('/[%s]/', self::PSR_6_RESERVED_CHARACTERS);
        return preg_replace($pattern, '.', $this->value ?? $this->name);
    }
}