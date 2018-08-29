<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160618105725 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `rulesets` ADD COLUMN `customer_id` int(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE `rulesets` ADD KEY `IDX_AE2A1BD99395C3F3` (`customer_id`)');
        $this->addSql('ALTER TABLE `rulesets` ADD CONSTRAINT `FK_AE2A1BD99395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
