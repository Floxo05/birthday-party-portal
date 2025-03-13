<?php

declare(strict_types=1);

namespace App\Form\FormHandling\NestedInputBag;

use Symfony\Component\HttpFoundation\ParameterBag;

class NestedInputBagFactory
{
    public function create(ParameterBag $bag): NestedInputBag
    {
        return new NestedInputBag($bag->all());
    }
}