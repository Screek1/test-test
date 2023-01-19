<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220725235816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE listing_master ADD PRIMARY KEY (feed_id, feed_listing_id)');
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE listing_master DROP PRIMARY KEY (feed_id, feed_listing_id)');
        // this down() migration is auto-generated, please modify it to your needs

    }
}
