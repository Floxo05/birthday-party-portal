<?php

declare(strict_types=1);

namespace App\Form\Party;

use App\Enum\ResponseStatus;
use Symfony\Component\Validator\Constraints as Assert;

class PartyResponseFormModel
{
    #[Assert\Range(min: 1, max: 2, notInRangeMessage: 'Bitte gib 1 oder 2 Begleitpersonen an.')]
    public ?int $plusGuests = null;

    public ?ResponseStatus $responseStatus = null;
}


