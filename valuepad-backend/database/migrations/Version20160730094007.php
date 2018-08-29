<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160730094007 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `rules` ADD COLUMN `client_address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `rules` ADD COLUMN `client_address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `rules` ADD COLUMN `client_zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `rules` ADD COLUMN `client_city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `rules` ADD COLUMN `client_state` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `rules` ADD KEY `IDX_899A993CFE4E4C` (`client_state`)');
        $this->addSql('ALTER TABLE `rules` ADD CONSTRAINT `FK_899A993CFE4E4C` FOREIGN KEY (`client_state`) REFERENCES `states` (`code`)');

        $this->addSql('ALTER TABLE `rules` ADD COLUMN `client_displayed_on_report_address1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `rules` ADD COLUMN `client_displayed_on_report_address2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `rules` ADD COLUMN `client_displayed_on_report_zip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `rules` ADD COLUMN `client_displayed_on_report_city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `rules` ADD COLUMN `client_displayed_on_report_state` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `rules` ADD KEY `IDX_899A993CB3FE9F18` (`client_displayed_on_report_state`)');
        $this->addSql('ALTER TABLE `rules` ADD CONSTRAINT `FK_899A993CB3FE9F18` FOREIGN KEY (`client_displayed_on_report_state`) REFERENCES `states` (`code`)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
