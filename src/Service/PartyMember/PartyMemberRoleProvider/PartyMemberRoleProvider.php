<?php

declare(strict_types=1);

namespace App\Service\PartyMember\PartyMemberRoleProvider;

use App\Entity\PartyMember;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionException;

class PartyMemberRoleProvider implements PartyMemberRoleProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return array<string>
     * @throws ReflectionException
     */
    public function getAvailableRoles(): array
    {
        $metadataFactory = $this->entityManager->getMetadataFactory();
        $allEntities = $metadataFactory->getAllMetadata();

        $roles = [];

        foreach ($allEntities as $entity)
        {
            $className = $entity->getName();

            if (is_subclass_of($className, PartyMember::class))
            {
                $shortName = (new ReflectionClass($className))->getShortName();
                $roles[] = $shortName;
            }
        }

        return $roles;
    }
}