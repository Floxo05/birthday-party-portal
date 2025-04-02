<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\FormHandling\NestedInputBag;

use App\Form\FormHandling\NestedInputBag\NestedInputBag;
use PHPUnit\Framework\TestCase;

class NestedInputBagTest extends TestCase
{
    public function testGetWithFlatKeyReturnsValue(): void
    {
        $bag = new NestedInputBag(['email' => 'test@example.com']);
        $this->assertSame('test@example.com', $bag->get('email'));
    }

    public function testGetWithNestedKeyReturnsValue(): void
    {
        $bag = new NestedInputBag([
            'user' => ['name' => 'Alice']
        ]);

        $this->assertSame('Alice', $bag->get('user.name'));
    }

    public function testGetReturnsDefaultIfKeyNotExists(): void
    {
        $bag = new NestedInputBag([]);
        $this->assertSame('fallback', $bag->get('nonexistent.key', 'fallback'));
    }

    public function testGetReturnsDefaultIfNestedKeyBreaks(): void
    {
        $bag = new NestedInputBag(['user' => 'not_an_array']);
        $this->assertSame('fallback', $bag->get('user.name', 'fallback'));
    }

    public function testGetArrayReturnsArrayIfPathIsArray(): void
    {
        $bag = new NestedInputBag(['settings' => ['options' => ['a' => 1, 'b' => 2]]]);

        $this->assertSame(['a' => 1, 'b' => 2], $bag->getArray('settings.options'));
    }

    public function testGetArrayReturnsDefaultIfPathIsNotArray(): void
    {
        $bag = new NestedInputBag(['settings' => ['options' => 'not_array']]);

        $this->assertSame([], $bag->getArray('settings.options'));
    }

    public function testGetArrayReturnsDefaultIfPathMissing(): void
    {
        $bag = new NestedInputBag([]);

        $this->assertSame(['default'], $bag->getArray('missing.path', ['default']));
    }
}
