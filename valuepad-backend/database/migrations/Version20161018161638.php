<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161018161638 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE `amc_customer_state_fees` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `state` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
              `fee_id` int(11) DEFAULT NULL,
              `amount` double NOT NULL,
              PRIMARY KEY (`id`),
              KEY `IDX_FCC0AF63A393D2FB` (`state`),
              KEY `IDX_FCC0AF63AB45AECA` (`fee_id`),
              CONSTRAINT `FK_FCC0AF63A393D2FB` FOREIGN KEY (`state`) REFERENCES `states` (`code`),
              CONSTRAINT `FK_FCC0AF63AB45AECA` FOREIGN KEY (`fee_id`) REFERENCES `customer_fees` (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $this->addSql('
            CREATE TABLE `amc_customer_county_fees` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `county_id` int(11) DEFAULT NULL,
              `fee_id` int(11) DEFAULT NULL,
              `amount` double NOT NULL,
              PRIMARY KEY (`id`),
              KEY `IDX_10E60E9385E73F45` (`county_id`),
              KEY `IDX_10E60E93AB45AECA` (`fee_id`),
              CONSTRAINT `FK_10E60E9385E73F45` FOREIGN KEY (`county_id`) REFERENCES `counties` (`id`),
              CONSTRAINT `FK_10E60E93AB45AECA` FOREIGN KEY (`fee_id`) REFERENCES `customer_fees` (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $this->addSql('
            CREATE TABLE `amc_customer_zip_fees` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `fee_id` int(11) DEFAULT NULL,
              `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
              `amount` double NOT NULL,
              PRIMARY KEY (`id`),
              KEY `IDX_1D29EE37AB45AECA` (`fee_id`),
              CONSTRAINT `FK_1D29EE37AB45AECA` FOREIGN KEY (`fee_id`) REFERENCES `customer_fees` (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
