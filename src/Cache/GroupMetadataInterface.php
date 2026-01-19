<?php

namespace Maiorano\ObjectCache\Cache;

interface GroupMetadataInterface extends GroupInterface
{
    public function getMetadata(): array;

    public function setMetadata(array $data): void;

    public function hasMetadata(string $key): bool;

    public function getMetadataValue(string $key): mixed;

    public function setMetadataValue(string $key, mixed $data): void;

    public function hasMetadataValue(string $key, mixed $value): bool;

    public function addMetadataValue(string $key, mixed $data): void;
}