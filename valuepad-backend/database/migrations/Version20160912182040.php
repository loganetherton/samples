<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160912182040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `orders` ADD COLUMN `fdic_id` int(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE `orders` ADD UNIQUE KEY `UNIQ_E52FFDEEC2DEBA1E` (`fdic_id`)');
        $this->addSql('ALTER TABLE `orders` ADD CONSTRAINT `FK_E52FFDEEC2DEBA1E` FOREIGN KEY (`fdic_id`) REFERENCES `fdic` (`id`)');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
