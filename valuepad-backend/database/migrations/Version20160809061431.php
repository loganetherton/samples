<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160809061431 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `reconsiderations` ADD COLUMN `document_id` int(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE `reconsiderations` ADD KEY `IDX_8DF4503EC33F7837` (`document_id`)');
        $this->addSql('ALTER TABLE `reconsiderations` ADD CONSTRAINT `FK_8DF4503EC33F7837` FOREIGN KEY (`document_id`) REFERENCES `order_additional_documents` (`id`)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
