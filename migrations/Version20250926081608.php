<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250926081608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat_message (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', party_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', room_owner_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', sender_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FAB3FC16213C1059 (party_id), INDEX IDX_FAB3FC16DEDAA13D (room_owner_id), INDEX IDX_FAB3FC16F624B39D (sender_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16DEDAA13D FOREIGN KEY (room_owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16213C1059');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16DEDAA13D');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16F624B39D');
        $this->addSql('DROP TABLE chat_message');
    }
}
