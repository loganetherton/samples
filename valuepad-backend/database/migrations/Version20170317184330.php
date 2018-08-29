<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170317184330 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE `availabilities_per_customer` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `customer_id` int(11) DEFAULT NULL,
              `user_id` int(11) DEFAULT NULL,
              `is_on_vacation` tinyint(1) NOT NULL,
              `from` datetime DEFAULT NULL,
              `to` datetime DEFAULT NULL,
              `message` longtext COLLATE utf8_unicode_ci,
              PRIMARY KEY (`id`),
              KEY `IDX_833025529395C3F3` (`customer_id`),
              KEY `IDX_83302552A76ED395` (`user_id`),
              CONSTRAINT `FK_833025529395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
              CONSTRAINT `FK_83302552A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $this->addSql('
            CREATE TABLE `customers_managers` (
              `customer_id` int(11) NOT NULL,
              `manager_id` int(11) NOT NULL,
              PRIMARY KEY (`customer_id`,`manager_id`),
              KEY `IDX_7915BE229395C3F3` (`customer_id`),
              KEY `IDX_7915BE22783E3463` (`manager_id`),
              CONSTRAINT `FK_7915BE22783E3463` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`),
              CONSTRAINT `FK_7915BE229395C3F3` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE `availabilities_per_customer`');
        $this->addSql('DROP TABLE `customers_managers`');

    }
}
