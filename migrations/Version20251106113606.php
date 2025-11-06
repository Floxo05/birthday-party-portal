<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251106113606 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchased_item ADD shop_item_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE purchased_item ADD CONSTRAINT FK_F8482141115C1274 FOREIGN KEY (shop_item_id) REFERENCES shop_item (id)');
        $this->addSql('CREATE INDEX IDX_F8482141115C1274 ON purchased_item (shop_item_id)');
        $this->addSql('ALTER TABLE shop_item ADD visible TINYINT(1) DEFAULT 1 NOT NULL, ADD max_per_user INT DEFAULT -1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchased_item DROP FOREIGN KEY FK_F8482141115C1274');
        $this->addSql('DROP INDEX IDX_F8482141115C1274 ON purchased_item');
        $this->addSql('ALTER TABLE purchased_item DROP shop_item_id');
        $this->addSql('ALTER TABLE shop_item DROP visible, DROP max_per_user');
    }
}
