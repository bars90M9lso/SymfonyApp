<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509212530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__tables AS SELECT id, num_table, description, max_guests FROM tables');
        $this->addSql('DROP TABLE tables');
        $this->addSql('CREATE TABLE tables (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, num_table INTEGER NOT NULL, description VARCHAR(255) DEFAULT NULL, max_guests INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO tables (id, num_table, description, max_guests) SELECT id, num_table, description, max_guests FROM __temp__tables');
        $this->addSql('DROP TABLE __temp__tables');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_84470221B1AA3E30 ON tables (num_table)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__tables AS SELECT id, num_table, description, max_guests FROM tables');
        $this->addSql('DROP TABLE tables');
        $this->addSql('CREATE TABLE tables (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, num_table INTEGER NOT NULL, description VARCHAR(255) DEFAULT NULL, max_guests INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO tables (id, num_table, description, max_guests) SELECT id, num_table, description, max_guests FROM __temp__tables');
        $this->addSql('DROP TABLE __temp__tables');
    }
}
