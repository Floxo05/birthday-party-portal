<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250814090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreshadowing flag to party';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE party ADD foreshadowing TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE party DROP foreshadowing');
    }
}


