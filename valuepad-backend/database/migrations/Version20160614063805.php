<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160614063805 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $constraint = $this->connection->fetchColumn('SELECT `CONSTRAINT_NAME`
FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
WHERE `TABLE_NAME`=? AND `COLUMN_NAME`=?', ['order_documents', 'primary_id']);

        $this->addSql('ALTER TABLE `order_documents` DROP FOREIGN KEY '.$constraint);
        $this->addSql('ALTER TABLE `order_documents` DROP COLUMN primary_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
