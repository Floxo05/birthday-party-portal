<?php

declare(strict_types=1);

namespace App\Service\Invitation\TokenGenerator;

interface TokenGeneratorInterface
{
    public function generate(): string;
}