<?php

declare(strict_types=1);

namespace App\Service\Invitation\InvitationTranslator;

use App\Entity\Invitation;

interface InvitationToStringTranslatorInterface
{
    public function translate(Invitation $invitation): string;
}