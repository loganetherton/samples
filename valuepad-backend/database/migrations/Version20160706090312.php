<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160706090312 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE `amc_licenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amc_id` int(11) DEFAULT NULL,
  `document_id` int(11) DEFAULT NULL,
  `number` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DE49DCB5A393D2FB` (`state`),
  KEY `IDX_DE49DCB5A35B8A6F` (`amc_id`),
  KEY `IDX_DE49DCB5C33F7837` (`document_id`),
  CONSTRAINT `FK_DE49DCB5A35B8A6F` FOREIGN KEY (`amc_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_DE49DCB5A393D2FB` FOREIGN KEY (`state`) REFERENCES `states` (`code`),
  CONSTRAINT `FK_DE49DCB5C33F7837` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

        $this->addSql('CREATE TABLE `amc_coverages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `county_id` int(11) DEFAULT NULL,
  `license_id` int(11) DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CCF730C585E73F45` (`county_id`),
  KEY `IDX_CCF730C5460F904B` (`license_id`),
  CONSTRAINT `FK_CCF730C5460F904B` FOREIGN KEY (`license_id`) REFERENCES `amc_licenses` (`id`),
  CONSTRAINT `FK_CCF730C585E73F45` FOREIGN KEY (`county_id`) REFERENCES `counties` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
