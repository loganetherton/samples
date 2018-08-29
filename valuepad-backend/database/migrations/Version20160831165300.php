<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160831165300 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE `amc_aliases` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                `address1` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
                `address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
                `city` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
                `state` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
                `zip` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
                `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                `fax` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
                `email` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `fk_state` (`state`),
                CONSTRAINT `fk_state` FOREIGN KEY (`state`) REFERENCES `states` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $this->addSql('
            ALTER TABLE `amc_licenses`
            ADD COLUMN `alias_id` INT(11) NULL DEFAULT NULL AFTER `expires_at`,
            ADD INDEX `index_alias` (`alias_id` ASC);
        ');
        $this->addSql('
            ALTER TABLE `amc_licenses`
            ADD CONSTRAINT `fk_alias`
            FOREIGN KEY (`alias_id`)
            REFERENCES `amc_aliases` (`id`) ON DELETE CASCADE;
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
