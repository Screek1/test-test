<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210217094017 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
            CREATE EXTENSION if not exists pg_trgm;

ALTER TABLE listing ADD COLUMN if not exists weighted_tsv tsvector;

UPDATE listing SET  
    weighted_tsv = l.weighted_tsv
FROM (  
    SELECT id,
           setweight(to_tsvector('english', COALESCE(mls_num,'')), 'A') ||
           setweight(to_tsvector('english', COALESCE(city,'') || ' ' || COALESCE(state_or_province,'')), 'B') ||
           setweight(to_tsvector('english', COALESCE(unparsed_address,'') || ' ' || COALESCE(city,'') || ' ' || COALESCE(state_or_province,'') || ' ' || COALESCE(postal_code,'')), 'C')
           AS weighted_tsv
     FROM listing
) AS l
WHERE l.id = listing.id;


CREATE OR REPLACE FUNCTION listing_weighted_tsv_trigger() RETURNS trigger AS $$
    BEGIN
      new.weighted_tsv :=
	     setweight(to_tsvector('english', COALESCE(mls_num,'')), 'A') ||
	     setweight(to_tsvector('english', COALESCE(city,'') || ' ' || COALESCE(state_or_province,'')), 'B') ||
	     setweight(to_tsvector('english', COALESCE(unparsed_address,'') || ' ' || COALESCE(city,'') || ' ' || COALESCE(state_or_province,'') || ' ' || COALESCE(postal_code,'')), 'C');
	  return new;
    END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER update_listing_weighted_tsv BEFORE INSERT OR UPDATE  
ON listing  
FOR EACH ROW EXECUTE PROCEDURE listing_weighted_tsv_trigger();


CREATE INDEX if not exists listing_mls_num_idx ON listing USING gin (mls_num gin_trgm_ops) where deleted_date is null and status in ('live', 'updated');
create index if not exists listing_city_idx on listing using gin (
  (COALESCE(city,'') || ' ' || COALESCE(state_or_province,'')) gin_trgm_ops
) where deleted_date is null and status in ('live', 'updated');
create index if not exists listing_address_idx on listing using gin (
  (COALESCE(unparsed_address,'') || ' ' || COALESCE(city,'') || ' ' || COALESCE(state_or_province,'') || ' ' || COALESCE(postal_code,'')) gin_trgm_ops
) where deleted_date is null and status in ('live', 'updated');

CREATE INDEX if not exists listing_weighted_tsv_idx ON listing USING gin (weighted_tsv);
        ");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("
            drop index if exists listing_weighted_tsv_idx;
drop index if exists listing_mls_num_idx;
drop index if exists listing_city_idx;
drop index if exists listing_address_idx;

drop trigger if exists update_listing_weighted_tsv on listing;
drop function if exists listing_weighted_tsv_trigger;
ALTER TABLE listing drop COLUMN if exists weighted_tsv;
drop EXTENSION if exists pg_trgm;
        ");
    }
}
