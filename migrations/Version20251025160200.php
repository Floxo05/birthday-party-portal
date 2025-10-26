<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251025160200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create game_config table to manage per-game start/end windows via EasyAdmin';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "CREATE TABLE game_config (
            id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
            slug VARCHAR(64) NOT NULL,
            start_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
            end_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
            UNIQUE INDEX uniq_game_config_slug (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE game_config');
    }
}
