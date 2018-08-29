<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161109155252 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('
            CREATE TABLE `branches` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `state` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
              `eo_id` int(11) DEFAULT NULL,
              `company_id` int(11) DEFAULT NULL,
              `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `is_default` tinyint(1) NOT NULL,
              `tin` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
              `address1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
              `address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
              `city` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
              `zip` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
              `assignment_zip` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `UNIQ_D760D16F6DB5F457` (`eo_id`),
              KEY `IDX_D760D16FA393D2FB` (`state`),
              KEY `IDX_D760D16F979B1AD6` (`company_id`),
              CONSTRAINT `FK_D760D16F6DB5F457` FOREIGN KEY (`eo_id`) REFERENCES `eo` (`id`),
              CONSTRAINT `FK_D760D16F979B1AD6` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
              CONSTRAINT `FK_D760D16FA393D2FB` FOREIGN KEY (`state`) REFERENCES `states` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
