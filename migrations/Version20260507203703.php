<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507203703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE list_guest (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, is_present BOOLEAN DEFAULT NULL, tables_id INTEGER DEFAULT NULL, CONSTRAINT FK_75240E3685405FD2 FOREIGN KEY (tables_id) REFERENCES tables (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_75240E3685405FD2 ON list_guest (tables_id)');
        $this->addSql('CREATE TABLE tables (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, num_table INTEGER NOT NULL, description VARCHAR(255) DEFAULT NULL, max_guests INTEGER DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE list_guest');
        $this->addSql('DROP TABLE tables');
    }
}
