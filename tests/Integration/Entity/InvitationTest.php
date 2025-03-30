<?php

declare(strict_types=1);

namespace App\Tests\Integration\Entity;

use App\Entity\Invitation;
use App\Service\PartyMember\PartyMemberRoleProvider\PartyMemberRoleProviderInterface;
use App\Validator\ValidPartyMemberRole\ValidPartyMemberRole;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InvitationTest extends KernelTestCase
{
    private ValidatorInterface $validator;
    private PartyMemberRoleProviderInterface $roleProvider;

    public function testValidRoles(): void
    {
        $validRoles = $this->roleProvider->getAvailableRoles();

        foreach ($validRoles as $role)
        {
            $entity = new Invitation();
            $entity->setRole($role);

            $violations = $this->validator->validate($entity);

            // violations are not empty, because of other constraints
            foreach ($violations as /** @var ConstraintViolation $violation */ $violation)
            {
                $this->assertNotSame(
                    ValidPartyMemberRole::class,
                    $violation->getConstraint()::class,
                    'A valid role should not produce validation errors.'
                );
            }
        }
    }

    public function testInvalidRole(): void
    {
        $entity = new Invitation();
        $entity->setRole('INVALID_ROLE'); // Ungültige Rolle setzen

        $violations = $this->validator->validate($entity);

        $hasInvalidRoleViolation = false;
        foreach ($violations as /** @var ConstraintViolation $violation */ $violation)
        {
            if ($violation->getConstraint()::class === ValidPartyMemberRole::class)
            {
                $hasInvalidRoleViolation = true;
                break;
            }
        }

        $this->assertTrue($hasInvalidRoleViolation, 'An invalid role should produce a validation error.');
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get('validator');
        $this->roleProvider = self::getContainer()->get(PartyMemberRoleProviderInterface::class);
    }
}