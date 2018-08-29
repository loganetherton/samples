<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160620133607 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `orders` ADD COLUMN `client_id` int(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE `orders` ADD COLUMN `client_displayed_on_report_id` int(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE `orders` ADD KEY `IDX_E52FFDEE19EB6921` (`client_id`)');
        $this->addSql('ALTER TABLE `orders` ADD KEY `IDX_E52FFDEE3F51F54B` (`client_displayed_on_report_id`)');
        $this->addSql('ALTER TABLE `orders` ADD CONSTRAINT `FK_E52FFDEE19EB6921` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)');
        $this->addSql('ALTER TABLE `orders` ADD CONSTRAINT `FK_E52FFDEE3F51F54B` FOREIGN KEY (`client_displayed_on_report_id`) REFERENCES `clients` (`id`)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
