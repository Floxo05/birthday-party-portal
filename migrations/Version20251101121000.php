<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251101121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shop_item table and points_spend column to party_member';
    }

    public function up(Schema $schema): void
    {
        // shop_item table
        $this->addSql("CREATE TABLE shop_item (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', party_id BINARY(16) DEFAULT NULL COMMENT '(DC2Type:uuid)', media_id BINARY(16) DEFAULT NULL COMMENT '(DC2Type:uuid)', name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price_points INT NOT NULL, quantity INT NOT NULL, INDEX IDX_DA39890C166D1F9C (party_id), INDEX IDX_DA39890CEA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql("ALTER TABLE shop_item ADD CONSTRAINT FK_DA39890C166D1F9C FOREIGN KEY (party_id) REFERENCES party (id)");
        $this->addSql("ALTER TABLE shop_item ADD CONSTRAINT FK_DA39890CEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)");
        // defaults for ints
        $this->addSql("ALTER TABLE shop_item ALTER price_points SET DEFAULT 0");
        $this->addSql("ALTER TABLE shop_item ALTER quantity SET DEFAULT 0");

        // party_member: points_spend column
        $this->addSql("ALTER TABLE party_member ADD points_spend INT DEFAULT 0 NOT NULL");
    }

    public function down(Schema $schema): void
    {
        // drop FK constraints first
        $this->addSql('ALTER TABLE shop_item DROP FOREIGN KEY FK_DA39890C166D1F9C');
        $this->addSql('ALTER TABLE shop_item DROP FOREIGN KEY FK_DA39890CEA9FDD75');
        $this->addSql('DROP TABLE shop_item');

        $this->addSql('ALTER TABLE party_member DROP points_spend');
    }
}
