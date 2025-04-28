<?php

declare(strict_types=1);

namespace App\Enum;

enum ResponseStatus: string
{
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case PENDING = 'pending';

    public function getLabel(): string
    {
        return match ($this)
        {
            self::ACCEPTED => 'Zugesagt',
            self::DECLINED => 'Abgesagt',
            self::PENDING => 'Offen',
        };
    }
}
