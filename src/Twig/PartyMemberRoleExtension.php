<?php

declare(strict_types=1);

namespace App\Twig;

use App\Service\PartyMember\PartyMemberRoleTranslator\PartyMemberRoleTranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PartyMemberRoleExtension extends AbstractExtension
{
    public function __construct(
        private readonly PartyMemberRoleTranslatorInterface $translator
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('translateRole', [$this, 'translateRole']),
        ];
    }

    public function translateRole(string $role): string
    {
        return $this->translator->translate($role);
    }
}
