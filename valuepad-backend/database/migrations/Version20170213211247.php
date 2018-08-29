<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170213211247 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE `reconsiderations_documents` (
              `reconsideration_id` int(11) NOT NULL,
              `document_id` int(11) NOT NULL,
              PRIMARY KEY (`reconsideration_id`,`document_id`),
              KEY `IDX_79A4B1EAA4C01FA9` (`reconsideration_id`),
              KEY `IDX_79A4B1EAC33F7837` (`document_id`),
              CONSTRAINT `FK_79A4B1EAA4C01FA9` FOREIGN KEY (`reconsideration_id`) REFERENCES `reconsiderations` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_79A4B1EAC33F7837` FOREIGN KEY (`document_id`) REFERENCES `order_additional_documents` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
