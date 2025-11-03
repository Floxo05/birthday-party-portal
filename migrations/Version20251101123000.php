<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251101123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create purchased_item table (copy of bought shop item per PartyMember)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE purchased_item (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', owner_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', media_id BINARY(16) DEFAULT NULL COMMENT '(DC2Type:uuid)', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, acquired_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_64EC40BB7E3C61F9 (owner_id), INDEX IDX_64EC40BBEA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("ALTER TABLE purchased_item ADD CONSTRAINT FK_64EC40BB7E3C61F9 FOREIGN KEY (owner_id) REFERENCES party_member (id)");
        $this->addSql("ALTER TABLE purchased_item ADD CONSTRAINT FK_64EC40BBEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE purchased_item DROP FOREIGN KEY FK_64EC40BB7E3C61F9');
        $this->addSql('ALTER TABLE purchased_item DROP FOREIGN KEY FK_64EC40BBEA9FDD75');
        $this->addSql('DROP TABLE purchased_item');
    }
}
