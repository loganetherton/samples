<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161214170732 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE `company_fees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_type_id` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E3FEDB825FA33B08` (`job_type_id`),
  KEY `IDX_E3FEDB82979B1AD6` (`company_id`),
  CONSTRAINT `FK_E3FEDB825FA33B08` FOREIGN KEY (`job_type_id`) REFERENCES `job_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E3FEDB82979B1AD6` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
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
