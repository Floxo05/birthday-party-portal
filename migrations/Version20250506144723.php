<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250506144723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_message_status (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', user_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', party_news_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', read_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_27A2274AA76ED395 (user_id), INDEX IDX_27A2274AA755C68B (party_news_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_message_status ADD CONSTRAINT FK_27A2274AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_message_status ADD CONSTRAINT FK_27A2274AA755C68B FOREIGN KEY (party_news_id) REFERENCES party_news (id)');
        $this->addSql('ALTER TABLE party_news ADD as_popup TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_message_status DROP FOREIGN KEY FK_27A2274AA76ED395');
        $this->addSql('ALTER TABLE user_message_status DROP FOREIGN KEY FK_27A2274AA755C68B');
        $this->addSql('DROP TABLE user_message_status');
        $this->addSql('ALTER TABLE party_news DROP as_popup');
    }
}
