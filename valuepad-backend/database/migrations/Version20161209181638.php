<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161209181638 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE `company_invitations` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `asc_appraiser_id` int(11) DEFAULT NULL,
              `branch_id` int(11) DEFAULT NULL,
              `email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
              `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`id`),
              KEY `IDX_3AF6A8365101E90B` (`asc_appraiser_id`),
              KEY `IDX_3AF6A836DCD6CC49` (`branch_id`),
              CONSTRAINT `FK_3AF6A8365101E90B` FOREIGN KEY (`asc_appraiser_id`) REFERENCES `asc_gov` (`id`),
              CONSTRAINT `FK_3AF6A836DCD6CC49` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`)
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
