<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class VersionService
{

    public function __construct(
        #[Autowire(env: 'APP_VERSION')]
        private string $appVersion,
    )
    {
    }

    public function getVersion(): string
    {
        return $this->appVersion;
    }
}