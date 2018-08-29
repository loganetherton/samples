<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161201160909 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('DROP TABLE branches_staff_privileges');

        $this->addSql('DROP TABLE companies_privileges');

        $this->addSql('RENAME TABLE branches_staff TO staff');

        $this->addSql('ALTER TABLE staff ADD COLUMN `privileges` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL');

        $this->addSql('DELETE FROM doctrine_migrations WHERE `version` IN ("20161117202006", "20161121195729", "20161128202053")');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
