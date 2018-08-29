<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170309182530 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('
            CREATE TABLE `bids_appraisers` (
              `bid_id` int(11) NOT NULL,
              `appraiser_id` int(11) NOT NULL,
              PRIMARY KEY (`bid_id`,`appraiser_id`),
              KEY `IDX_C40045534D9866B8` (`bid_id`),
              KEY `IDX_C400455398218044` (`appraiser_id`),
              CONSTRAINT `FK_C40045534D9866B8` FOREIGN KEY (`bid_id`) REFERENCES `bids` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_C400455398218044` FOREIGN KEY (`appraiser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $this->addSql('
            CREATE TABLE `orders_subassignees` (
              `order_id` int(11) NOT NULL,
              `appraiser_id` int(11) NOT NULL,
              PRIMARY KEY (`order_id`,`appraiser_id`),
              KEY `IDX_9602766B8D9F6D38` (`order_id`),
              KEY `IDX_9602766B98218044` (`appraiser_id`),
              CONSTRAINT `FK_9602766B8D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
              CONSTRAINT `FK_9602766B98218044` FOREIGN KEY (`appraiser_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE `bids_appraisers`');
        $this->addSql('DROP TABLE `orders_subassignees`');
    }
}
