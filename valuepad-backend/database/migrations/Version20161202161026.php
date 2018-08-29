<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161202161026 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `staff` DROP COLUMN `privileges`');
        $this->addSql('ALTER TABLE `staff` DROP COLUMN `role`');

        $this->addSql('ALTER TABLE `staff` ADD COLUMN `is_admin` tinyint(1) NOT NULL');
        $this->addSql('ALTER TABLE `staff` ADD COLUMN `is_rfp_manager` tinyint(1) NOT NULL');
        $this->addSql('ALTER TABLE `staff` ADD COLUMN `is_manager` tinyint(1) NOT NULL');
        $this->addSql('ALTER TABLE `staff` ADD COLUMN `company_id` int(11) DEFAULT NULL');

        $this->addSql('ALTER TABLE `staff` ADD KEY `IDX_426EF392979B1AD6` (`company_id`)');
        $this->addSql('ALTER TABLE `staff` ADD CONSTRAINT `IDX_426EF392979B1AD6` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
