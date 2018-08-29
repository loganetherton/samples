<?php
namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160719195654 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE authorize_net_references ADD COLUMN `masked_credit_card_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE authorize_net_references ADD COLUMN `masked_account_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE authorize_net_references ADD COLUMN `masked_routing_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE authorize_net_references ADD COLUMN `name_on_account` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE authorize_net_references ADD COLUMN `bank_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE authorize_net_references ADD COLUMN `account_type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
