<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160704130650 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
        
        CREATE TABLE `customers_amcs` (
  `customer_id` int(11) NOT NULL,
  `amc_id` int(11) NOT NULL,
  PRIMARY KEY (`customer_id`,`amc_id`),
  KEY `IDX_8673CF869395C3F3` (`customer_id`),
  KEY `IDX_8673CF86A35B8A6F` (`amc_id`),
  CONSTRAINT `FK_8673CF869395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_8673CF86A35B8A6F` FOREIGN KEY (`amc_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        
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
