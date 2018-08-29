<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160718142953 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("UPDATE `properties` SET owner_interest=CONCAT('[\"', owner_interest, '\"]') WHERE owner_interest IS NOT NULL");
        $this->addSql("UPDATE `properties` SET owner_interest='[]' WHERE owner_interest IS NULL");
        $this->addSql('ALTER TABLE `properties` CHANGE owner_interest owner_interests varchar(255) COLLATE utf8_unicode_ci NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
