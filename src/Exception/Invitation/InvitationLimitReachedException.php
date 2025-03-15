<?php

declare(strict_types=1);

namespace App\Exception\Invitation;

class InvitationLimitReachedException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Diese Einladung wurde bereits zu oft genutzt.");
    }
}