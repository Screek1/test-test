<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210405060528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE price_log (id SERIAL NOT NULL, listing_id INT NOT NULL, date date NOT NULL, price DOUBLE PRECISION NOT NULL, city_average DOUBLE PRECISION DEFAULT NULL, subdivision_average DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))'
        );
        $this->addSql('CREATE INDEX price_log_listing_id_idx ON price_log (listing_id)');
        $this->addSql('CREATE UNIQUE INDEX price_log_listing_id_date_idx ON price_log (listing_id, date)');
        $this->addSql(
            'ALTER TABLE price_log ADD CONSTRAINT price_log_listing_id_listing_id_fk FOREIGN KEY (listing_id) REFERENCES listing (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE price_log');
    }
}
