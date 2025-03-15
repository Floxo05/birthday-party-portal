<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250315120544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE invitation (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', party_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', role VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', max_uses INT NOT NULL, uses INT NOT NULL, UNIQUE INDEX UNIQ_F11D61A25F37A13B (token), INDEX IDX_F11D61A2213C1059 (party_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invitation DROP FOREIGN KEY FK_F11D61A2213C1059');
        $this->addSql('DROP TABLE invitation');
    }
}
