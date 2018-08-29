<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160810110640 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `users` ADD COLUMN `ach_id` int(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE `users` ADD KEY `IDX_1483A5E9CB6AB30F` (`ach_id`)');
        $this->addSql('ALTER TABLE `users` ADD CONSTRAINT `FK_1483A5E9CB6AB30F` FOREIGN KEY (`ach_id`) REFERENCES `ach` (`id`)');

        $this->addSql('UPDATE `users` SET `ach_id`=(SELECT id FROM `ach` WHERE appraiser_id=`users`.`id`) WHERE `type`="appraiser"');

        $constraint = $this->connection->fetchColumn('SELECT `CONSTRAINT_NAME`
FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
WHERE `TABLE_NAME`=? AND `COLUMN_NAME`=?', ['ach', 'appraiser_id']);

        $this->addSql('ALTER TABLE `ach` DROP FOREIGN KEY '.$constraint);

        $this->addSql('ALTER TABLE `ach` DROP COLUMN appraiser_id');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
