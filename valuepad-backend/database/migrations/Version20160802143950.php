<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160802143950 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE users ADD COLUMN `full_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql("UPDATE users SET full_name=CONCAT(first_name, ' ', last_name) WHERE `type`='appraiser'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
