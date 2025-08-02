<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427112027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE party_news (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', party_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', media_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', text LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_2705883213C1059 (party_id), INDEX IDX_2705883EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE party_news ADD CONSTRAINT FK_2705883213C1059 FOREIGN KEY (party_id) REFERENCES party (id)'
        );
        $this->addSql(
            'ALTER TABLE party_news ADD CONSTRAINT FK_2705883EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)'
        );
        $this->addSql('ALTER TABLE party ADD rsvp_deadline DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\'');
        $this->addSql('ALTER TABLE party_member ADD response_status VARCHAR(255) DEFAULT \'pending\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party_news DROP FOREIGN KEY FK_2705883213C1059');
        $this->addSql('ALTER TABLE party_news DROP FOREIGN KEY FK_2705883EA9FDD75');
        $this->addSql('DROP TABLE party_news');
        $this->addSql('ALTER TABLE party_member DROP response_status');
        $this->addSql('ALTER TABLE party DROP rsvp_deadline');
    }
}
