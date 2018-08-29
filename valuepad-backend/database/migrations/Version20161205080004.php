<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161205080004 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $token = $this->getToken('devices', 'user_id');
        $this->addSql('ALTER TABLE `devices` DROP FOREIGN KEY '.$token);
        $this->addSql('ALTER TABLE `devices` ADD CONSTRAINT '.$token.' FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');

        $token = $this->getToken('sessions', 'user_id');
        $this->addSql('ALTER TABLE `sessions` DROP FOREIGN KEY '.$token);
        $this->addSql('ALTER TABLE `sessions` ADD CONSTRAINT '.$token.' FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');

        $token = $this->getToken('tokens', 'user_id');
        $this->addSql('ALTER TABLE `tokens` DROP FOREIGN KEY '.$token);
        $this->addSql('ALTER TABLE `tokens` ADD CONSTRAINT '.$token.' FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');

        $token = $this->getToken('transactions', 'owner_id');
        $this->addSql('ALTER TABLE `transactions` DROP FOREIGN KEY '.$token);
        $this->addSql('ALTER TABLE `transactions` ADD CONSTRAINT '.$token.' FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');

        $token = $this->getToken('authorize_net_references', 'owner_id');
        $this->addSql('ALTER TABLE `authorize_net_references` DROP FOREIGN KEY '.$token);
        $this->addSql('ALTER TABLE `authorize_net_references` ADD CONSTRAINT '.$token.' FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');
    }

    /**
     * @param string $table
     * @param string $column
     * @return string
     */
    private function getToken($table, $column)
    {
        return $this->connection->fetchColumn('SELECT `CONSTRAINT_NAME`
FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
WHERE `TABLE_NAME`=? AND `COLUMN_NAME`=?', [$table, $column]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
