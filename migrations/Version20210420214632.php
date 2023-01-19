<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210420214632 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD client_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD oauth_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER password DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER phone_number DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER phone_code DROP NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP client_id');
        $this->addSql('ALTER TABLE "user" DROP oauth_type');
        $this->addSql('ALTER TABLE "user" ALTER password SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER phone_number SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER phone_code SET NOT NULL');
    }
}
