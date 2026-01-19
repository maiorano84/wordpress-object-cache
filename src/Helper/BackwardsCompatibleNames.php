<?php

namespace Maiorano\ObjectCache\Helper;

trait BackwardsCompatibleNames
{
    public function add_multiple(array $data, string $group = 'default', int $expire = 0): iterable
    {
        return $this->addMultiple($data, $group, $expire);
    }

    public function set_multiple(array $data, string $group = 'default', int $expire = 0): iterable
    {
        return $this->setMultiple($data, $group, $expire);
    }

    public function get_multiple(array $keys, string $group = 'default', bool $force = false): iterable
    {
        return $this->getMultiple($keys, $group, $force);
    }

    public function delete_multiple(array $keys, string $group = 'default'): iterable
    {
        return $this->deleteMultiple($keys, $group);
    }

    public function flush_group(string $group): bool
    {
        return $this->flushGroup($group);
    }

    public function add_global_groups(string|array $groups): void
    {
        $this->addGroup($groups, 'global');
    }

    public function add_non_persistent_groups(string|array $groups): void
    {
        $this->addGroup($groups, 'non_persistent');
    }

    public function switch_to_blog(int $blogId): void
    {
        $this->setPrefix($blogId);
    }
}