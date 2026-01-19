<?php

namespace Maiorano\ObjectCache\Cache;

final class BlogKeyResolver implements KeyResolverInterface
{
    private readonly bool $multisite;
    private readonly string $separator;
    private int|string $blogId;
    private string $keyType;

    public function __construct(
        bool       $multisite = false,
        int|string $blogId = '',
        string     $separator = KeyResolverInterface::DEFAULT_KEY_SEPARATOR,
        string     $keyType = Key::class,
    )
    {
        $this->multisite = $multisite;
        $this->separator = $separator;
        $this->blogId = $blogId;
        $this->keyType = $keyType;
    }

    public function resolve(string $key, GroupInterface $group): CacheKeyInterface
    {
        $parts = $this->resolveParts($key, $group);
        return new $this->keyType($key, implode($this->separator, $parts));
    }

    private function resolveParts(string $key, GroupMetadataInterface $group): array
    {
        if ($this->multisite && !$group->hasMetadataValue('type', 'global')) {
            $key = $this->blogId . $this->separator . $key;
        }
        return [$group, $key];
    }

    public function setPrefix(int|string $prefix): void
    {
        $this->blogId = $prefix;
    }
}