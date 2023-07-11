<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230707123738 extends AbstractMigration
{
    private array $eventNames = [];

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, postcode VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, status_id INT NOT NULL, venue_id INT NOT NULL, organiser_id INT NOT NULL, name VARCHAR(255) NOT NULL, start_date_time DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', duration INT NOT NULL, registration_deadline DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', max_participants SMALLINT NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_3BAE0AA76BF700BD (status_id), INDEX IDX_3BAE0AA740A73EBA (venue_id), INDEX IDX_3BAE0AA7A0631C12 (organiser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_user (event_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_92589AE271F7E88B (event_id), INDEX IDX_92589AE2A76ED395 (user_id), PRIMARY KEY(event_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, site_id INT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(100) NOT NULL, phone VARCHAR(15) DEFAULT NULL, email VARCHAR(100) NOT NULL, administrator TINYINT(1) DEFAULT 0 NOT NULL, active TINYINT(1) DEFAULT 1 NOT NULL, filename VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649F6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venue (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, name VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, INDEX IDX_91911B0D8BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA76BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA740A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A0631C12 FOREIGN KEY (organiser_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_92589AE271F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_92589AE2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE venue ADD CONSTRAINT FK_91911B0D8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');

        // set up MySQL events for cron tasks
        $this->addSql('SET GLOBAL event_scheduler = ON');

        /*
        Le cron démarre à l'heure suivante H:00:00 (e.g. il est 10h26 -> démarre à 11h00),
        pour passer uniquement à des heures exactes (contrainte à la création de l'évènement : heure exacte).
        On évite ainsi d'avoir des fenêtres de temps durant lesquelles l'état de l'évènement n'est pas en concordance
        avec ses dates et heures. Notamment, on évite de laisser ouvert un évènement dont la date limite d'inscription
        est dépassée, ce qui pourrait être le cas pendant une fenêtre pouvant aller jusqu'à une heure si le cron
        se déclenchait à des heures arbitraires.
        */
        $priority = 0;
        $this->addSql($this->makeStatusUpdateMySQLEvent(++$priority,
            'closed',
            'NOW() >= registration_deadline',
            'open'));
        $this->addSql($this->makeStatusUpdateMySQLEvent(++$priority,
            'in progress',
            'NOW() BETWEEN start_date_time AND DATE_ADD(start_date_time, INTERVAL duration MINUTE)',
            'closed'));
        $this->addSql($this->makeStatusUpdateMySQLEvent(++$priority,
            'past',
            'NOW() > DATE_ADD(start_date_time, INTERVAL duration MINUTE)',
            'in progress'));
        $this->addSql($this->makeStatusUpdateMySQLEvent(++$priority,
            'archived',
            'NOW() > DATE_ADD(start_date_time, INTERVAL duration / (60 * 24) + 30 DAY)',
            'created', 'cancelled', 'past'));
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA76BF700BD');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA740A73EBA');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7A0631C12');
        $this->addSql('ALTER TABLE event_user DROP FOREIGN KEY FK_92589AE271F7E88B');
        $this->addSql('ALTER TABLE event_user DROP FOREIGN KEY FK_92589AE2A76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F6BD1646');
        $this->addSql('ALTER TABLE venue DROP FOREIGN KEY FK_91911B0D8BAC62AF');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE event_user');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE venue');
        $this->addSql('DROP TABLE messenger_messages');
        foreach ($this->eventNames as $eventName) {
            $this->addSql("DROP EVENT $eventName");
        }
    }

    private function makeStatusUpdateMySQLEvent(int $executionPriority, string $targetState, string $condition, string ...$sourceStates): string
    {
        $targetState = strtolower($targetState);
        $eventName = str_pad($executionPriority . '', 2, '0', STR_PAD_LEFT) . "_set_status_" . str_replace(" ", "", $targetState);
        $this->eventNames[] = $eventName;
        $targetState = strtoupper($targetState); // superfluous

        $sql = "CREATE EVENT $eventName";
        $sql = $this->addNewline($sql);
        $sql .= 'ON SCHEDULE EVERY 1 HOUR';
        $sql = $this->addNewline($sql);
        $sql .= "STARTS STR_TO_DATE(CONCAT(CURRENT_DATE, ' ', CONCAT(HOUR(CURRENT_TIME) + 1, ':00:00')), '%Y-%m-%d %H:%i:%s')";
        $sql = $this->addNewline($sql);
        $sql .= 'DO';
        $sql = $this->addNewline($sql);
        $sql .= "UPDATE event";
        $sql = $this->addNewline($sql);
        $sql .= "SET status_id = (SELECT id FROM status WHERE name = '$targetState')";
        $sql = $this->addNewline($sql);
        $sql .= "WHERE status_id IN(SELECT id FROM status WHERE name IN('";
        $sql .= implode("', '", $sourceStates);
        $sql .= "'))";
        $sql = $this->addNewline($sql);
        $sql .= "AND $condition";

        return $sql;
    }

    private function addNewline(string $s)
    {
        return $s . "\n";
    }
}
