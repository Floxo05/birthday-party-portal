<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926160610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add isFoodVotingGroup boolean field to party_group table';
    }

    public function up(Schema $schema): void
    {
        // Add isFoodVotingGroup column to party_group table
        $this->addSql('ALTER TABLE party_group ADD is_food_voting_group TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // Remove isFoodVotingGroup column from party_group table
        $this->addSql('ALTER TABLE party_group DROP COLUMN is_food_voting_group');
    }
}
