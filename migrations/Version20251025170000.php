<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251025170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add closed flag to game_config to allow early closure/finalization';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE game_config ADD closed TINYINT(1) DEFAULT '0' NOT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE game_config DROP closed');
    }
}
