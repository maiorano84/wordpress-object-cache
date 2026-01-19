<?php

namespace Maiorano\ObjectCache\Tests\Unit\Cache;

use Maiorano\ObjectCache\Cache\Psr6Key;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Psr6Key::class)]
class Psr6KeyTest extends TestCase
{

    #[DataProvider('psr6KeyDataProvider')]
    public function testGetValue(string $key, string $expected)
    {
        $k = new Psr6Key($key);
        $this->assertEquals($expected, $k->getValue());
    }

    public static function psr6KeyDataProvider(): array
    {
        return [
            ['key', 'key'],
            ['1:key', '1.key'],
        ];
    }
}
