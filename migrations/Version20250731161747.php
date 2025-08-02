<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250731161747 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_message_status DROP FOREIGN KEY FK_27A2274AA755C68B');
        $this->addSql(
            'ALTER TABLE user_message_status ADD CONSTRAINT FK_27A2274AA755C68B FOREIGN KEY (party_news_id) REFERENCES party_news (id) ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_message_status DROP FOREIGN KEY FK_27A2274AA755C68B');
        $this->addSql(
            'ALTER TABLE user_message_status ADD CONSTRAINT FK_27A2274AA755C68B FOREIGN KEY (party_news_id) REFERENCES party_news (id)'
        );
    }
}
