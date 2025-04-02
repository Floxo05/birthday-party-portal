<?php

declare(strict_types=1);

namespace App\Tests\Unit\Validator\ValidPartyMemberRole;

use App\Service\PartyMember\PartyMemberRoleProvider\PartyMemberRoleProviderInterface;
use App\Validator\ValidPartyMemberRole\ValidPartyMemberRole;
use App\Validator\ValidPartyMemberRole\ValidPartyMemberRoleValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ValidPartyMemberRoleValidatorTest extends TestCase
{
    public function testDoesNotAddViolationForValidRole(): void
    {
        $provider = $this->createMock(PartyMemberRoleProviderInterface::class);
        $provider->method('getAvailableRoles')->willReturn(['Host', 'Guest']);

        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->never())->method('buildViolation');

        $validator = new ValidPartyMemberRoleValidator($provider);
        $validator->initialize($context);

        $validator->validate('Host', new ValidPartyMemberRole());
    }

    public function testAddsViolationForInvalidRole(): void
    {
        $provider = $this->createMock(PartyMemberRoleProviderInterface::class);
        $provider->method('getAvailableRoles')->willReturn(['Host', 'Guest']);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('{{ value }}', 'InvalidRole')
            ->willReturnSelf();

        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $context = $this->createMock(ExecutionContext::class);
        $context
            ->expects($this->once())
            ->method('buildViolation')
            ->with('The role "{{ value }}" is not a valid party member role.')
            ->willReturn($violationBuilder);

        $validator = new ValidPartyMemberRoleValidator($provider);
        $validator->initialize($context);

        $validator->validate('InvalidRole', new ValidPartyMemberRole());
    }

    public function testIgnoresNonStringValues(): void
    {
        $provider = $this->createMock(PartyMemberRoleProviderInterface::class);
        $provider->method('getAvailableRoles')->willReturn(['Host', 'Guest']);

        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->never())->method('buildViolation');

        $validator = new ValidPartyMemberRoleValidator($provider);
        $validator->initialize($context);

        $validator->validate(123, new ValidPartyMemberRole());
        $validator->validate(null, new ValidPartyMemberRole());
    }

    public function testSkipsValidationIfWrongConstraintType(): void
    {
        $provider = $this->createMock(PartyMemberRoleProviderInterface::class);

        $context = $this->createMock(ExecutionContext::class);
        $context->expects($this->never())->method('buildViolation');

        $validator = new ValidPartyMemberRoleValidator($provider);
        $validator->initialize($context);

        // Gib z.B. einen leeren Stub als falscher Constraint-Typ
        $validator->validate('Host', $this->createMock(\Symfony\Component\Validator\Constraint::class));
    }
}
