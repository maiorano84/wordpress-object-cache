<?php


namespace Integration\Cache;

use Maiorano\ObjectCache\Cache\BlogKeyResolver;
use Maiorano\ObjectCache\Cache\Group;
use Maiorano\ObjectCache\Cache\GroupInterface;
use Maiorano\ObjectCache\Cache\Key;
use Maiorano\ObjectCache\Cache\KeyResolverInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BlogKeyResolver::class)]
#[CoversClass(Group::class)]
#[CoversClass(Key::class)]
class BlogKeyResolverTest extends TestCase
{
    public static function resolverProvider(): array
    {
        $group = new Group('default');
        return [
            [new BlogKeyResolver(), 'test', $group, 'default:test'],
            [new BlogKeyResolver(true, 1), 'test', $group, 'default:1:test'],
        ];
    }

    #[DataProvider('resolverProvider')]
    public function testResolve(
        KeyResolverInterface $resolver,
        string               $k,
        GroupInterface       $g,
        string               $expectation,
    )
    {
        $this->assertEquals($expectation, $resolver->resolve($k, $g));
    }

    public function testSetPrefix()
    {
        $resolver = new BlogKeyResolver(true, 1);

        $resolver->setPrefix(2);
        $this->assertEquals('default:2:test', $resolver->resolve('test', new Group('default')));
    }
}
