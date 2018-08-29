<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170125203735 extends AbstractMigration
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
        ', ['notification_subscriptions', 'appraiser_id']);

        $this->addSql('
            ALTER TABLE `notification_subscriptions`
            DROP FOREIGN KEY '.$constraint
        );

        $this->addSql('
            ALTER TABLE `notification_subscriptions`
            CHANGE COLUMN `appraiser_id` `assignee_id` INT(11) NULL DEFAULT NULL;
        ');

        $this->addSql('
            ALTER TABLE `notification_subscriptions`
            ADD CONSTRAINT `FK_52C540C859EC7D60`
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
