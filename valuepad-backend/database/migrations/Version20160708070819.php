<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160708070819 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE `amc_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_type_id` int(11) DEFAULT NULL,
  `amc_id` int(11) DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL,
  `amount` double NOT NULL,
  `qualifier` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_26F669F75FA33B08` (`job_type_id`),
  KEY `IDX_26F669F7A35B8A6F` (`amc_id`),
  CONSTRAINT `FK_26F669F75FA33B08` FOREIGN KEY (`job_type_id`) REFERENCES `job_types` (`id`),
  CONSTRAINT `FK_26F669F7A35B8A6F` FOREIGN KEY (`amc_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');
        
        $this->addSql('
        CREATE TABLE `amc_state_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fee_id` int(11) DEFAULT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_EAC61A7EA393D2FB` (`state`),
  KEY `IDX_EAC61A7EAB45AECA` (`fee_id`),
  CONSTRAINT `FK_EAC61A7EA393D2FB` FOREIGN KEY (`state`) REFERENCES `states` (`code`),
  CONSTRAINT `FK_EAC61A7EAB45AECA` FOREIGN KEY (`fee_id`) REFERENCES `amc_fees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            
        ');

        $this->addSql('
        
            CREATE TABLE `amc_zip_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_id` int(11) DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D751C326AB45AECA` (`fee_id`),
  CONSTRAINT `FK_D751C326AB45AECA` FOREIGN KEY (`fee_id`) REFERENCES `amc_fees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');


        $this->addSql('
            CREATE TABLE `amc_county_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `county_id` int(11) DEFAULT NULL,
  `fee_id` int(11) DEFAULT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_73F664FF85E73F45` (`county_id`),
  KEY `IDX_73F664FFAB45AECA` (`fee_id`),
  CONSTRAINT `FK_73F664FF85E73F45` FOREIGN KEY (`county_id`) REFERENCES `counties` (`id`),
  CONSTRAINT `FK_73F664FFAB45AECA` FOREIGN KEY (`fee_id`) REFERENCES `amc_fees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
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
