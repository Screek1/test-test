<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210219030722 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE OR REPLACE FUNCTION listing_weighted_tsv_trigger() RETURNS trigger AS $$
    BEGIN
      new.weighted_tsv :=
	     setweight(to_tsvector('english', COALESCE(new.mls_num,'')), 'A') ||
	     setweight(to_tsvector('english', COALESCE(new.city,'') || ' ' || COALESCE(new.state_or_province,'')), 'B') ||
	     setweight(to_tsvector('english', COALESCE(new.unparsed_address,'') || ' ' || COALESCE(new.city,'') || ' ' || COALESCE(new.state_or_province,'') || ' ' || COALESCE(new.postal_code,'')), 'C');
	  return new;
    END;
$$ LANGUAGE plpgsql;");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
