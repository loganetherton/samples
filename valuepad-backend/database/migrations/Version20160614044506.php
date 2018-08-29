<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use PDO;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160614044506 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE `order_documents_primaries` (
  `document_id` int(11) NOT NULL,
  `primary_document_id` int(11) NOT NULL,
  PRIMARY KEY (`document_id`,`primary_document_id`),
  KEY `IDX_EE0022CC33F7837` (`document_id`),
  KEY `IDX_EE0022C67D018B6` (`primary_document_id`),
  CONSTRAINT `FK_EE0022C67D018B6` FOREIGN KEY (`primary_document_id`) REFERENCES `documents` (`id`),
  CONSTRAINT `FK_EE0022CC33F7837` FOREIGN KEY (`document_id`) REFERENCES `order_documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

        $statement = $this->connection->prepare('SELECT primary_id, id FROM `order_documents`');

        $statement->execute();

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)){
            $this->addSql(
                'INSERT INTO `order_documents_primaries` (document_id, primary_document_id) VALUES(?,?)',
                [$row['id'], $row['primary_id']]
            );
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
