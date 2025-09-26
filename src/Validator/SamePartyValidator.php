<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\PartyGroupAssignment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SamePartyValidator extends ConstraintValidator
{
    /**
     * @param SameParty $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof SameParty)
        {
            throw new UnexpectedTypeException($constraint, SameParty::class);
        }

        // Get the root object (PartyGroupAssignment) from the execution context
        $root = $this->context->getRoot();
        
        if (!$root instanceof PartyGroupAssignment)
        {
            return;
        }

        $group = $root->getGroup();
        $member = $root->getPartyMember();

        if ($group === null || $member === null)
        {
            return;
        }

        $groupParty = $group->getParty();
        $memberParty = $member->getParty();

        if ($groupParty === null || $memberParty === null)
        {
            return;
        }

        if ($groupParty->getId() === null || $memberParty->getId() === null)
        {
            return;
        }

        if ($groupParty->getId()->equals($memberParty->getId()))
        {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath('partyMember')
            ->addViolation();
    }
}


