<?php

declare(strict_types=1);

namespace App\Security;

enum Role: string
{
    case ADMIN = 'ROLE_ADMIN';
    case ORGANIZER = 'ROLE_ORGANIZER';
    case USER = 'ROLE_USER';

    /**
     * @return Role[]
     */
    public static function assignable(): array
    {
        return [
            self::ORGANIZER,
            self::ADMIN,
        ];
    }

    public function label(): string
    {
        return match ($this)
        {
            self::ADMIN => 'Administrator',
            self::ORGANIZER => 'Veranstalter',
            self::USER => 'Nutzer',
        };
    }
}
