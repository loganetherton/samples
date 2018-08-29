<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170522165233 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            ALTER TABLE `request_logs`
            ADD COLUMN `code` int(11) NOT NULL,
            ADD COLUMN `order_id` int(11) DEFAULT NULL,
            ADD COLUMN `sender_id` int(11) DEFAULT NULL,
            ADD COLUMN `recipient_id` int(11) DEFAULT NULL,
            ADD COLUMN `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            ADD COLUMN `event` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
            ADD KEY `IDX_8F28E1A6F624B39D` (`sender_id`),
            ADD KEY `IDX_8F28E1A6E92F8F78` (`recipient_id`),
            ADD KEY `index_story_order` (`order_id`),
            ADD KEY `index_story_type` (`type`),
            ADD KEY `index_story_event` (`event`),
            ADD CONSTRAINT `FK_8F28E1A6E92F8F78` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`),
            ADD CONSTRAINT `FK_8F28E1A6F624B39D` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`)
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
