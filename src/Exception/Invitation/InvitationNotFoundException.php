<?php

declare(strict_types=1);

namespace App\Exception\Invitation;

class InvitationNotFoundException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Die Einladung existiert nicht.");
    }
}