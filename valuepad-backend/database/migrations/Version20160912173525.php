<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160912173525 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE `fdic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fin` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `task_order` varchar(4) COLLATE utf8_unicode_ci DEFAULT NULL,
  `asset_number` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `asset_type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `line` smallint(6) DEFAULT NULL,
  `contractor` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
