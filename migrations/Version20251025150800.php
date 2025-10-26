<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251025150800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create game_score table for Clash mini-games';
    }

    public function up(Schema $schema): void
    {
        // game_score table
        $this->addSql(
            "CREATE TABLE game_score (
            id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
            game_slug VARCHAR(64) NOT NULL,
            party_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
            party_member_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)',
            best_score INT NOT NULL DEFAULT 0,
            attempts INT NOT NULL DEFAULT 0,
            last_submitted_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
            applied_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
            UNIQUE INDEX uniq_game_party_member (game_slug, party_id, party_member_id),
            INDEX IDX_GS_PARTY (party_id),
            INDEX IDX_GS_PARTY_MEMBER (party_member_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB"
        );

        $this->addSql(
            "ALTER TABLE game_score ADD CONSTRAINT FK_GS_PARTY FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE"
        );
        $this->addSql(
            "ALTER TABLE game_score ADD CONSTRAINT FK_GS_PARTY_MEMBER FOREIGN KEY (party_member_id) REFERENCES party_member (id) ON DELETE CASCADE"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE game_score');
    }
}
