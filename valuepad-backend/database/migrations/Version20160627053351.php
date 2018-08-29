<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160627053351 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `orders` ADD COLUMN `created_at` datetime DEFAULT NULL');
        $this->addSql('ALTER TABLE `orders` ADD COLUMN `updated_at` datetime DEFAULT NULL');

        $this->addSql('ALTER TABLE `users` ADD COLUMN `created_at` datetime DEFAULT NULL');
        $this->addSql('ALTER TABLE `users` ADD COLUMN `updated_at` datetime DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
