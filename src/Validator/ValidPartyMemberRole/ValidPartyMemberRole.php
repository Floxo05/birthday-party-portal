<?php

declare(strict_types=1);

namespace App\Validator\ValidPartyMemberRole;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class ValidPartyMemberRole extends Constraint
{
    public string $message = 'The role "{{ value }}" is not a valid party member role.';
}