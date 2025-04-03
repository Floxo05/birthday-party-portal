<?php
declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use LogicException;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DatabaseTestCase extends KernelTestCase
{
    protected KernelBrowser $kernelBrowser;
    protected EntityManagerInterface $entityManager;
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        if ('test' !== $kernel->getEnvironment())
        {
            throw new LogicException('Execution only in Test environment possible!');
        }

        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);

        $metaData = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->updateSchema($metaData);
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        unset($this->kernelBrowser);
        unset($this->entityManager);
    }
}