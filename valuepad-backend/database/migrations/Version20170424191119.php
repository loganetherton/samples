<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170424191119 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE `receipt_documents` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `order_id` int(11) DEFAULT NULL,
              `document_id` int(11) DEFAULT NULL,
              `created_at` datetime NOT NULL,
              PRIMARY KEY (`id`),
              KEY `IDX_8E33FA968D9F6D38` (`order_id`),
              KEY `IDX_8E33FA96C33F7837` (`document_id`),
              CONSTRAINT `FK_8E33FA968D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
              CONSTRAINT `FK_8E33FA96C33F7837` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`)
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
