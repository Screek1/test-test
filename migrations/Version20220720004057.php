<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220720004057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE listing ADD COLUMN class_id VARCHAR(20) NULL');
        $this->addSql('UPDATE listing SET class_id = \'RD_1\' where feed_id = \'idx\'');
        $this->addSql('ALTER TABLE feed ADD COLUMN class_id VARCHAR(20) NULL');
        $this->addSql('UPDATE feed SET class_id = \'RD_1\' where name = \'idx\'');
        $this->addSql('INSERT INTO feed (name, busy, last_run_time, class_id) VALUES (\'idx\', FALSE, NOW(), \'RA_2\')');
        $this->addSql('INSERT INTO feed (name, busy, last_run_time, class_id) VALUES (\'idx\', FALSE, NOW(), \'MF_3\')');
        $this->addSql('INSERT INTO feed (name, busy, last_run_time, class_id) VALUES (\'idx\', FALSE, NOW(), \'LD_4\')');
        $this->addSql('INSERT INTO feed (name, busy, last_run_time, class_id) VALUES (\'idx\', FALSE, NOW(), \'RT_5\')');
        $this->addSql("ALTER TABLE listing_master drop COLUMN id");
        $this->addSql('ALTER TABLE listing_master ADD COLUMN class_id VARCHAR(20) NULL');


    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE listing drop COLUMN class_id");
        $this->addSql("ALTER TABLE feed drop COLUMN class_id");
        $this->addSql('DELETE FROM feed WHERE class_id = \'RA_2\'');
        $this->addSql('DELETE FROM feed WHERE class_id = \'MF_3\'');
        $this->addSql('DELETE FROM feed WHERE class_id = \'LD_4\'');
        $this->addSql('DELETE FROM feed WHERE class_id = \'RT_5\'');
        $this->addSql('DELETE FROM feed WHERE class_id = \'RT_5\'');
        $this->addSql('ALTER TABLE listing_master ADD COLUMN id SERIAL NOT NULL, PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE listing_master drop COLUMN class_id');

    }
}
