<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220325175949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE viewing ALTER COLUMN user_id TYPE bigint');
        $this->addSql('ALTER TABLE viewing ALTER COLUMN listing_id TYPE bigint');
        $this->addSql('ALTER TABLE favorite_listings ALTER COLUMN user_id TYPE bigint');
        $this->addSql('ALTER TABLE favorite_listings ALTER COLUMN listing_id TYPE bigint');
        $this->addSql('ALTER TABLE price_log ALTER COLUMN listing_id TYPE bigint');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE viewing ALTER COLUMN user_id TYPE integer');
        $this->addSql('ALTER TABLE viewing ALTER COLUMN listing_id TYPE integer ');
        $this->addSql('ALTER TABLE favorite_listings ALTER COLUMN user_id TYPE integer');
        $this->addSql('ALTER TABLE favorite_listings ALTER COLUMN listing_id TYPE integer ');
        $this->addSql('ALTER TABLE price_log ALTER COLUMN listing_id TYPE integer ');
    }
}
