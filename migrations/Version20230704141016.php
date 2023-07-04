<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230704141016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE event_user (event_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_92589AE271F7E88B (event_id), INDEX IDX_92589AE2A76ED395 (user_id), PRIMARY KEY(event_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_92589AE271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_92589AE2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD state_id INT NOT NULL, ADD venue_id INT NOT NULL, ADD organiser_id INT NOT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA75D83CC1 FOREIGN KEY (state_id) REFERENCES state (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA740A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A0631C12 FOREIGN KEY (organiser_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA75D83CC1 ON event (state_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA740A73EBA ON event (venue_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7A0631C12 ON event (organiser_id)');
        $this->addSql('ALTER TABLE user CHANGE phone phone VARCHAR(15) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_user DROP FOREIGN KEY FK_92589AE271F7E88B');
        $this->addSql('ALTER TABLE event_user DROP FOREIGN KEY FK_92589AE2A76ED395');
        $this->addSql('DROP TABLE event_user');
        $this->addSql('ALTER TABLE user CHANGE phone phone VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA75D83CC1');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA740A73EBA');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7A0631C12');
        $this->addSql('DROP INDEX IDX_3BAE0AA75D83CC1 ON event');
        $this->addSql('DROP INDEX IDX_3BAE0AA740A73EBA ON event');
        $this->addSql('DROP INDEX IDX_3BAE0AA7A0631C12 ON event');
        $this->addSql('ALTER TABLE event DROP state_id, DROP venue_id, DROP organiser_id');
    }
}
