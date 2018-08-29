<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160716211148 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `authorize_net_references` CHANGE `holder_id` `owner_id` int(11) DEFAULT NULL');
        $this->addSql('ALTER TABLE `authorize_net_references` CHANGE `payment_profile_id` `credit_card_profile_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('ALTER TABLE `authorize_net_references` ADD COLUMN `bank_account_profile_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
