<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260420104620 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__quest AS SELECT id, title, description, reward_exp, reward_gold, required_level, status, player_id FROM quest');
        $this->addSql('DROP TABLE quest');
        $this->addSql('CREATE TABLE quest (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(120) NOT NULL, description CLOB NOT NULL, reward_exp INTEGER NOT NULL, reward_gold INTEGER NOT NULL, required_level INTEGER NOT NULL, status VARCHAR(255) NOT NULL, player_id INTEGER DEFAULT NULL, CONSTRAINT FK_4317F81799E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO quest (id, title, description, reward_exp, reward_gold, required_level, status, player_id) SELECT id, title, description, reward_exp, reward_gold, required_level, status, player_id FROM __temp__quest');
        $this->addSql('DROP TABLE __temp__quest');
        $this->addSql('CREATE INDEX IDX_4317F81799E6F5DF ON quest (player_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__quest AS SELECT id, title, description, reward_exp, reward_gold, required_level, status, player_id FROM quest');
        $this->addSql('DROP TABLE quest');
        $this->addSql('CREATE TABLE quest (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(120) NOT NULL, description CLOB NOT NULL, reward_exp INTEGER NOT NULL, reward_gold INTEGER NOT NULL, required_level INTEGER NOT NULL, status VARCHAR(255) NOT NULL, player_id INTEGER NOT NULL, CONSTRAINT FK_4317F81799E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO quest (id, title, description, reward_exp, reward_gold, required_level, status, player_id) SELECT id, title, description, reward_exp, reward_gold, required_level, status, player_id FROM __temp__quest');
        $this->addSql('DROP TABLE __temp__quest');
        $this->addSql('CREATE INDEX IDX_4317F81799E6F5DF ON quest (player_id)');
    }
}
