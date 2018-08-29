<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161219161808 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE `journal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT \'(DC2Type:json_array)\',
  `response` longtext COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\',
  `error` longtext COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
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
