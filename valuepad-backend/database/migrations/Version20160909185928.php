<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160909185928 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $constraint = $this->connection->fetchColumn('
            SELECT `CONSTRAINT_NAME`
            FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
            WHERE `TABLE_NAME`=? AND `COLUMN_NAME`=?
        ', ['customer_fees', 'appraiser_id']);

        $this->addSql('
            ALTER TABLE `customer_fees`
            DROP FOREIGN KEY '.$constraint
        );

        $this->addSql('
            ALTER TABLE `customer_fees`
            CHANGE COLUMN `appraiser_id` `assignee_id` INT(11) NULL DEFAULT NULL;
        ');

        $this->addSql('
            ALTER TABLE `customer_fees`
            ADD CONSTRAINT `FK_F402651B98218044`
            FOREIGN KEY (`assignee_id`)
            REFERENCES `users` (`id`);
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
