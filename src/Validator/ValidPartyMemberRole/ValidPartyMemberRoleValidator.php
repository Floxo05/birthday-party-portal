<?php

declare(strict_types=1);

namespace App\Validator\ValidPartyMemberRole;

use App\Service\PartyMember\PartyMemberRoleProvider\PartyMemberRoleProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

#[Autoconfigure(public: true)] // todo remove if used

class ValidPartyMemberRoleValidator extends ConstraintValidator
{

    public function __construct(
        private readonly PartyMemberRoleProviderInterface $roleProvider
    ) {
    }

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidPartyMemberRole)
        {
            return;
        }

        $validRoles = $this->roleProvider->getAvailableRoles();

        if (!in_array($value, $validRoles, true))
        {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}