<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210306075017 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('create index if not exists listing_state_or_province_idx on listing (state_or_province) where deleted_date is null');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('drop index if exists listing_state_or_province_idx');
    }
}
