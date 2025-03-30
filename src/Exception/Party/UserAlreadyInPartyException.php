<?php

declare(strict_types=1);

namespace App\Exception\Party;

class UserAlreadyInPartyException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Der Benutzer ist bereits Mitglied der Party.");
    }
}