<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210310075809 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE "user" ADD address TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD state VARCHAR(3) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD city TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD postal VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD about TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ALTER phone_number DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP address');
        $this->addSql('ALTER TABLE "user" DROP state');
        $this->addSql('ALTER TABLE "user" DROP city');
        $this->addSql('ALTER TABLE "user" DROP postal');
        $this->addSql('ALTER TABLE "user" DROP about');
        $this->addSql('ALTER TABLE "user" ALTER phone_number SET DEFAULT \'00000000000\'');
    }
}
