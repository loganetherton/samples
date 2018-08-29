<?php
namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160808180025 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE `contacts` CHANGE `full_name` `display_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL');
        $this->addSql('UPDATE `contacts` SET `display_name`=`name` WHERE `name` IS NOT NULL');
        $this->addSql('UPDATE `contacts` SET `display_name`=TRIM(CONCAT(first_name, " ", middle_name, " ", last_name)) WHERE `name` IS NULL AND middle_name IS NOT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
