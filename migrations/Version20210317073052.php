<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210317073052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX if exists viewing_user_id_listing_id_idx');
        $this->addSql(
            'CREATE UNIQUE INDEX if not exists viewing_user_id_listing_id_status_idx ON viewing (user_id, listing_id, status) WHERE (status = \'new\')'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX if exists viewing_user_id_listing_id_status_idx');
        $this->addSql(
            'CREATE UNIQUE INDEX if not exists viewing_user_id_listing_id_idx ON viewing (user_id, listing_id)'
        );
    }
}
