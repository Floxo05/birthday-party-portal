<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521084153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE invitation (id UUID NOT NULL, party_id UUID DEFAULT NULL, role VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, max_uses INT NOT NULL, uses INT NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F11D61A25F37A13B ON invitation (token)');
        $this->addSql('CREATE INDEX IDX_F11D61A2213C1059 ON invitation (party_id)');
        $this->addSql('COMMENT ON COLUMN invitation.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN invitation.party_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN invitation.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'CREATE TABLE media (id UUID NOT NULL, party_id UUID DEFAULT NULL, uploader_id UUID DEFAULT NULL, original_filename VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, size INT NOT NULL, storage_path VARCHAR(255) NOT NULL, uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_6A2CA10C213C1059 ON media (party_id)');
        $this->addSql('CREATE INDEX IDX_6A2CA10C16678C77 ON media (uploader_id)');
        $this->addSql('COMMENT ON COLUMN media.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN media.party_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN media.uploader_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN media.uploaded_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'CREATE TABLE party (id UUID NOT NULL, party_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, title VARCHAR(255) NOT NULL, rsvp_deadline DATE DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('COMMENT ON COLUMN party.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN party.rsvp_deadline IS \'(DC2Type:date_immutable)\'');
        $this->addSql(
            'CREATE TABLE party_member (id UUID NOT NULL, party_id UUID DEFAULT NULL, user_id UUID DEFAULT NULL, response_status VARCHAR(255) DEFAULT \'pending\' NOT NULL, discr VARCHAR(255) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_36928121213C1059 ON party_member (party_id)');
        $this->addSql('CREATE INDEX IDX_36928121A76ED395 ON party_member (user_id)');
        $this->addSql('COMMENT ON COLUMN party_member.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN party_member.party_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN party_member.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql(
            'CREATE TABLE party_news (id UUID NOT NULL, party_id UUID DEFAULT NULL, media_id UUID DEFAULT NULL, text TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, as_popup BOOLEAN DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_2705883213C1059 ON party_news (party_id)');
        $this->addSql('CREATE INDEX IDX_2705883EA9FDD75 ON party_news (media_id)');
        $this->addSql('COMMENT ON COLUMN party_news.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN party_news.party_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN party_news.media_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN party_news.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'CREATE TABLE "user" (id UUID NOT NULL, username VARCHAR(180) NOT NULL, name VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON "user" (username)');
        $this->addSql('COMMENT ON COLUMN "user".id IS \'(DC2Type:uuid)\'');
        $this->addSql(
            'CREATE TABLE user_message_status (id UUID NOT NULL, user_id UUID DEFAULT NULL, party_news_id UUID DEFAULT NULL, read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_27A2274AA76ED395 ON user_message_status (user_id)');
        $this->addSql('CREATE INDEX IDX_27A2274AA755C68B ON user_message_status (party_news_id)');
        $this->addSql('COMMENT ON COLUMN user_message_status.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_message_status.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_message_status.party_news_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_message_status.read_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql(
            'CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;'
        );
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql(
            'CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();'
        );
        $this->addSql(
            'ALTER TABLE invitation ADD CONSTRAINT FK_F11D61A2213C1059 FOREIGN KEY (party_id) REFERENCES party (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C213C1059 FOREIGN KEY (party_id) REFERENCES party (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C16678C77 FOREIGN KEY (uploader_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE party_member ADD CONSTRAINT FK_36928121213C1059 FOREIGN KEY (party_id) REFERENCES party (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE party_member ADD CONSTRAINT FK_36928121A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE party_news ADD CONSTRAINT FK_2705883213C1059 FOREIGN KEY (party_id) REFERENCES party (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE party_news ADD CONSTRAINT FK_2705883EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE user_message_status ADD CONSTRAINT FK_27A2274AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->addSql(
            'ALTER TABLE user_message_status ADD CONSTRAINT FK_27A2274AA755C68B FOREIGN KEY (party_news_id) REFERENCES party_news (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE invitation DROP CONSTRAINT FK_F11D61A2213C1059');
        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_6A2CA10C213C1059');
        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_6A2CA10C16678C77');
        $this->addSql('ALTER TABLE party_member DROP CONSTRAINT FK_36928121213C1059');
        $this->addSql('ALTER TABLE party_member DROP CONSTRAINT FK_36928121A76ED395');
        $this->addSql('ALTER TABLE party_news DROP CONSTRAINT FK_2705883213C1059');
        $this->addSql('ALTER TABLE party_news DROP CONSTRAINT FK_2705883EA9FDD75');
        $this->addSql('ALTER TABLE user_message_status DROP CONSTRAINT FK_27A2274AA76ED395');
        $this->addSql('ALTER TABLE user_message_status DROP CONSTRAINT FK_27A2274AA755C68B');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE party');
        $this->addSql('DROP TABLE party_member');
        $this->addSql('DROP TABLE party_news');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_message_status');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
