<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160802070358 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            'CREATE TABLE `attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT \'(DC2Type:json_array)\',
  `quantity` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `attempted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
