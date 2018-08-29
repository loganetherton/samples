<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160713193219 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
       $this->addSql('
      CREATE TABLE `amc_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amc_id` int(11) DEFAULT NULL,
  `document_id` int(11) DEFAULT NULL,
  `from` datetime NOT NULL,
  `to` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `is_paid` tinyint(1) NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_CB54FC1FA35B8A6F` (`amc_id`),
  KEY `IDX_CB54FC1FC33F7837` (`document_id`),
  CONSTRAINT `FK_CB54FC1FA35B8A6F` FOREIGN KEY (`amc_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_CB54FC1FC33F7837` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
       
       ');

        $this->addSql('
           CREATE TABLE `amc_invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `file_number` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `loan_number` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `borrower_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ordered_at` datetime NOT NULL,
  `completed_at` datetime NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FCCB0DA58D9F6D38` (`order_id`),
  KEY `IDX_FCCB0DA52989F1FD` (`invoice_id`),
  CONSTRAINT `FK_FCCB0DA52989F1FD` FOREIGN KEY (`invoice_id`) REFERENCES `amc_invoices` (`id`),
  CONSTRAINT `FK_FCCB0DA58D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci       
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
