<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926154908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE food_vote (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', group_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', party_member_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', food_item LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_EF372DB5FE54D947 (group_id), INDEX IDX_EF372DB56357B2B3 (party_member_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE food_vote ADD CONSTRAINT FK_EF372DB5FE54D947 FOREIGN KEY (group_id) REFERENCES party_group (id)');
        $this->addSql('ALTER TABLE food_vote ADD CONSTRAINT FK_EF372DB56357B2B3 FOREIGN KEY (party_member_id) REFERENCES party_member (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE food_vote DROP FOREIGN KEY FK_EF372DB5FE54D947');
        $this->addSql('ALTER TABLE food_vote DROP FOREIGN KEY FK_EF372DB56357B2B3');
        $this->addSql('DROP TABLE food_vote');
    }
}
