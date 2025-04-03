<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\FormHandling\NestedInputBag;

use App\Form\FormHandling\NestedInputBag\NestedInputBag;
use App\Form\FormHandling\NestedInputBag\NestedInputBagFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class NestedInputBagFactoryTest extends TestCase
{
    public function testCreatesNestedInputBagWithCorrectData(): void
    {
        $parameterBag = new ParameterBag([
            'user' => ['name' => 'Alice'],
            'email' => 'test@example.com',
        ]);

        $factory = new NestedInputBagFactory();
        $nested = $factory->create($parameterBag);

        $this->assertInstanceOf(NestedInputBag::class, $nested);
        $this->assertSame('Alice', $nested->get('user.name'));
        $this->assertSame('test@example.com', $nested->get('email'));
    }

    public function testChangesToOriginalDoNotAffectCreatedBag(): void
    {
        $original = new ParameterBag(['key' => 'value']);
        $factory = new NestedInputBagFactory();

        $nested = $factory->create($original);
        $original->set('key', 'modified');

        $this->assertSame('value', $nested->get('key')); // bleibt bei ursprünglichem Wert
    }
}
