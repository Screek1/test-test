<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210312073754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE if not exists saved_search (id SERIAL NOT NULL, user_id INT NOT NULL, last_run TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, frequency TEXT NOT NULL, criteria JSONB NOT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX if not exists saved_search_user_id_idx ON saved_search (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE if exists saved_search');
    }
}
