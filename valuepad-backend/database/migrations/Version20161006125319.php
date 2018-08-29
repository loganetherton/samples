<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161006125319 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `authorize_net_references` ADD COLUMN `address` varchar(100) COLLATE utf8_unicode_ci NOT NULL');
        $this->addSql('ALTER TABLE `authorize_net_references` ADD COLUMN `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL');
        $this->addSql('ALTER TABLE `authorize_net_references` ADD COLUMN `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL');
        $this->addSql('ALTER TABLE `authorize_net_references` ADD COLUMN `state` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL');

        $this->addSql('ALTER TABLE `authorize_net_references` ADD KEY `FK_FD7C8E44A393D2FB` (`state`)');
        $this->addSql('ALTER TABLE `authorize_net_references` ADD CONSTRAINT `FK_FD7C8E44A393D2FB` FOREIGN KEY (`state`) REFERENCES `states` (`code`)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
