<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926110152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE party_group (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', party_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, INDEX IDX_7205646A213C1059 (party_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE party_group_assignment (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', group_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', party_member_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', INDEX IDX_9D1B0DD6FE54D947 (group_id), INDEX IDX_9D1B0DD66357B2B3 (party_member_id), UNIQUE INDEX uniq_group_member (group_id, party_member_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE party_group ADD CONSTRAINT FK_7205646A213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE party_group_assignment ADD CONSTRAINT FK_9D1B0DD6FE54D947 FOREIGN KEY (group_id) REFERENCES party_group (id)');
        $this->addSql('ALTER TABLE party_group_assignment ADD CONSTRAINT FK_9D1B0DD66357B2B3 FOREIGN KEY (party_member_id) REFERENCES party_member (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party_group DROP FOREIGN KEY FK_7205646A213C1059');
        $this->addSql('ALTER TABLE party_group_assignment DROP FOREIGN KEY FK_9D1B0DD6FE54D947');
        $this->addSql('ALTER TABLE party_group_assignment DROP FOREIGN KEY FK_9D1B0DD66357B2B3');
        $this->addSql('DROP TABLE party_group');
        $this->addSql('DROP TABLE party_group_assignment');
    }
}
