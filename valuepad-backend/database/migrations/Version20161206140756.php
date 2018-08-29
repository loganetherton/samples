<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161206140756 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manager_id` int(11) DEFAULT NULL,
  `appraiser_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2DEDCC6F783E3463` (`manager_id`),
  KEY `IDX_2DEDCC6F98218044` (`appraiser_id`),
  CONSTRAINT `FK_2DEDCC6F783E3463` FOREIGN KEY (`manager_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_2DEDCC6F98218044` FOREIGN KEY (`appraiser_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
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
