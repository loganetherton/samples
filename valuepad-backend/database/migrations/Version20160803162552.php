<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160803162552 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `logs` ADD COLUMN `customer_id` int(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE `logs` ADD KEY `IDX_F08FC65C9395C3F3` (`customer_id`)');
        $this->addSql('ALTER TABLE `logs` ADD CONSTRAINT `FK_F08FC65C9395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`)');

        $this->addSql('UPDATE logs SET customer_id=user_id WHERE order_id IS NULL');
        $this->addSql('UPDATE logs SET customer_id=(SELECT customer_id FROM orders WHERE id=order_id) WHERE order_id IS NOT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
