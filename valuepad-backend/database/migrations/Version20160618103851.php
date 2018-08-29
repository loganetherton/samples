<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160618103851 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE `orders_rulesets` (
  `order_id` int(11) NOT NULL,
  `ruleset_id` int(11) NOT NULL,
  PRIMARY KEY (`order_id`,`ruleset_id`),
  KEY `IDX_690CC61F8D9F6D38` (`order_id`),
  KEY `IDX_690CC61F54F1C144` (`ruleset_id`),
  CONSTRAINT `FK_690CC61F54F1C144` FOREIGN KEY (`ruleset_id`) REFERENCES `rulesets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_690CC61F8D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
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
