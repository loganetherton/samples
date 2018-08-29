<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use ValuePad\Support\Tracker;
use ArrayIterator;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160729185933 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->connection->exec('
            CREATE TABLE `rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `available` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT \'(DC2Type:json_array)\',
  `require_env` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        ');


        $data = $this->connection->fetchAll('SELECT id, rules FROM rulesets');

        $tracker = new Tracker(new ArrayIterator($data), 100);

        foreach ($tracker as $row){

            if (!$this->connection->isTransactionActive()){
                $this->connection->beginTransaction();
            }

            $value = json_decode($row['rules'], true);
            $requireEnv = $value['requireEnv'] ?? null;

            $flag = null;

            if ($requireEnv === true) {
                $flag = 1;
            }

            if ($requireEnv === false){
                $flag = 0;
            }

            $this->connection->insert('rules', [
                'available' => $requireEnv === null ? '[]' : '["requireEnv"]',
                'require_env' => $flag
            ]);

            $id = $this->connection->lastInsertId();

            $this->connection->update('rulesets', ['rules' => $id], ['id' => $row['id']]);

            if ($tracker->isTime()){
                $this->connection->commit();
            }
        }

        if ($this->connection->isTransactionActive()){
            $this->connection->commit();
        }

        $this->connection->exec('ALTER TABLE rulesets CHANGE `rules` `rules_id` int(11) DEFAULT NULL');
        $this->connection->exec('ALTER TABLE rulesets ADD UNIQUE KEY `UNIQ_AE2A1BD9FB699244` (`rules_id`)');
        $this->connection->exec('ALTER TABLE rulesets ADD CONSTRAINT `FK_AE2A1BD9FB699244` FOREIGN KEY (`rules_id`) REFERENCES `rules` (`id`)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
