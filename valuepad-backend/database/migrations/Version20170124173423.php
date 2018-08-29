<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170124173423 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `orders` ADD COLUMN `staff_id` int(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE `orders` ADD KEY `IDX_E52FFDEED4D57CD` (`staff_id`)');
        $this->addSql('ALTER TABLE `orders` ADD CONSTRAINT `FK_E52FFDEED4D57CD` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
