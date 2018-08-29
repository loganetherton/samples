<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170303182644 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE `orders` ADD COLUMN `loan_amount` double DEFAULT NULL
        ');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE `orders` DROP COLUMN `loan_amount`
        ');

    }
}
