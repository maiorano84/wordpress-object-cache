<?php

namespace Integration\Cache;

use Maiorano\ObjectCache\Cache\BlogKeyResolver;
use Maiorano\ObjectCache\Cache\Group;
use Maiorano\ObjectCache\Cache\GroupInterface;
use Maiorano\ObjectCache\Cache\GroupManager;
use Maiorano\ObjectCache\Cache\GroupManagerInterface;
use Maiorano\ObjectCache\Cache\GroupMetadataInterface;
use Maiorano\ObjectCache\Cache\Key;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GroupManager::class)]
#[CoversClass(BlogKeyResolver::class)]
#[CoversClass(Group::class)]
#[CoversClass(Key::class)]
class GroupManagerTest extends TestCase
{
    private GroupManagerInterface $manager;

    public function setUp(): void
    {
        $this->manager = new GroupManager(new BlogKeyResolver);
    }

    public function testOffsetSetString()
    {
        $this->manager['groupString'] = 'testString';

        /**
         * @var GroupMetadataInterface $g
         */
        $g = $this->manager['groupString'];

        $this->assertInstanceOf(GroupInterface::class, $g);
        $this->assertEquals('testString', $g);
        $this->assertEmpty($g);
        $this->assertEmpty($g->getMetadata());
    }

    public function testOffsetSetArray()
    {
        $this->manager['groupArray'] = [
            ['key' => new Key('testKey')],
            ['type' => ['unit']]
        ];

        /**
         * @var GroupMetadataInterface $g
         */
        $g = $this->manager['groupArray'];

        $this->assertInstanceOf(GroupInterface::class, $g);
        $this->assertEquals('groupArray', $g);
        $this->assertCount(1, $g);
        $this->assertTrue($g->hasMetadataValue('type', 'unit'));
    }

    public function testOffsetSetGroup()
    {
        $g = new Group(
            'testObject',
            ['key' => new Key('testKey')],
            ['type' => ['unit']],
        );
        $this->manager['group'] = $g;

        $this->assertEquals('testObject', $g);
        $this->assertCount(1, $g);
        $this->assertTrue($g->hasMetadataValue('type', 'unit'));
    }

    public function testGetKeyAndAdd()
    {
        $key = $this->manager->getKey('foo', 'bar', true);
        $this->assertSame($key, $this->manager['foo']['foo:bar']);
    }

    public function testGetInvalidName()
    {
        $nothing = $this->manager->get('');
        $whitespace = $this->manager->get(' ');

        $this->assertEquals('default', (string)$nothing);
        $this->assertEquals('default', $whitespace->getName());

        $all = iterator_to_array($this->manager->getIterator());
        $this->assertArrayHasKey('default', $all);
        $this->assertCount(1, $this->manager);
    }
}