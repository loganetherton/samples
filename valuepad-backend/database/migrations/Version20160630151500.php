<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160630151500 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `properties` ADD COLUMN `latitude` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `properties` ADD COLUMN `longitude` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
