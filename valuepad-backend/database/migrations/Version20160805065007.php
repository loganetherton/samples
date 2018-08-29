<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160805065007 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE `amc_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amc_id` int(11) DEFAULT NULL,
  `push_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_443E734FA35B8A6F` (`amc_id`),
  CONSTRAINT `FK_443E734FA35B8A6F` FOREIGN KEY (`amc_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

        $this->addSql('INSERT INTO `amc_settings` (amc_id, push_url) SELECT id, NULL FROM users WHERE `type`="amc"');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
