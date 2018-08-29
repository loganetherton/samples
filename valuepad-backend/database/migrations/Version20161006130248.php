<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161006130248 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $data = $this->connection->query('SELECT * FROM authorize_net_references')->fetchAll();

        $users = $this->connection->query('SELECT * FROM `users` WHERE `id` IN ('.implode(',', array_column($data, 'owner_id')).')')
            ->fetchAll();

        $users = array_column($users, null, 'id');

        foreach ($data as $row){
            $user = $users[$row['owner_id']];

            $this->connection->update('authorize_net_references', [
                'address' => $user['address1'],
                'city' => $user['city'],
                'state' => $user['state'],
                'zip' => $user['zip']
            ], ['owner_id' => $row['owner_id']]);
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
