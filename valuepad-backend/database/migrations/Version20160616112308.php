<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160616112308 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C82E74A393D2FB` (`state`),
  KEY `IDX_C82E749395C3F3` (`customer_id`),
  CONSTRAINT `FK_C82E749395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_C82E74A393D2FB` FOREIGN KEY (`state`) REFERENCES `states` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
