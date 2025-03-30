<?php

declare(strict_types=1);

namespace App\Exception\Invitation;

class InvitationExpiredException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Diese Einladung ist abgelaufen.");
    }
}