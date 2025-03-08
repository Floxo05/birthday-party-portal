<?php
declare(strict_types=1);

namespace App\Tests\Integration\Trait;

use App\Entity\PartyPackage\Party;

trait PartyTrait
{
    public function getNewParty(): Party
    {
        $party = new Party();
        $party
            ->setTitle('Test Party')
            ->setPartyDate(new \DateTime());

        return $party;
    }
}