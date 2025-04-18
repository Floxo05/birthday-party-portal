<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250413094302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE media (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', party_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', uploader_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, size INT NOT NULL, storage_path VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6A2CA10C213C1059 (party_id), INDEX IDX_6A2CA10C16678C77 (uploader_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C213C1059 FOREIGN KEY (party_id) REFERENCES party (id)'
        );
        $this->addSql(
            'ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C16678C77 FOREIGN KEY (uploader_id) REFERENCES user (id)'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C213C1059');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C16678C77');
        $this->addSql('DROP TABLE media');
    }
}
