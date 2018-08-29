<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160705170656 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        foreach (['client', 'client_displayed_on_report'] as $key){
            $this->addSql('ALTER TABLE `orders` DROP COLUMN `'.$key.'_name`');
            $this->addSql('ALTER TABLE `orders` DROP COLUMN `'.$key.'_city`');
            $this->addSql('ALTER TABLE `orders` DROP COLUMN `'.$key.'_zip`');
            $this->addSql('ALTER TABLE `orders` DROP COLUMN `'.$key.'_address1`');
            $this->addSql('ALTER TABLE `orders` DROP COLUMN `'.$key.'_address2`');

            $constraint = $this->connection->fetchColumn('SELECT `CONSTRAINT_NAME`
FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
WHERE `TABLE_NAME`=? AND `COLUMN_NAME`=?', ['orders', $key.'_state']);

            $this->addSql('ALTER TABLE `orders` DROP FOREIGN KEY '.$constraint);
            $this->addSql('ALTER TABLE `orders` DROP COLUMN `'.$key.'_state`');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
