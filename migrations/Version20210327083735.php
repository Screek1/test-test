<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210327083735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            "create materialized view if not exists city_stats_view AS select city, state_or_province, count(*) as count
from listing where deleted_date is null
  and status in ('live', 'updated') 
group by city, state_or_province"
        );
        $this->addSql(
            "create unique index if not exists city_stats_view_city_state_or_province_idx on city_stats_view (city, state_or_province)"
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("drop materialized view city_stats_view");
    }
}
