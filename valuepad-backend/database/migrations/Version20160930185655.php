<?php
namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160930185655 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE `companies` (		
-                `id` int(11) NOT NULL AUTO_INCREMENT,		
-                `state` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,		
-                `eo_id` int(11) DEFAULT NULL,		
-                `w9_id` int(11) DEFAULT NULL,		
-                `ach_id` int(11) DEFAULT NULL,		
-                `creator_id` int(11) DEFAULT NULL,		
-                `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,		
-                `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,		
-                `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,		
-                `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,		
-                `phone` varchar(20) COLLATE utf8_unicode_ci NOT NULL,		
-                `fax` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,		
-                `address1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,		
-                `address2` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,		
-                `city` varchar(100) COLLATE utf8_unicode_ci NOT NULL,		
-                `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,		
-                `assignment_zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,		
-                `type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,		
-                `tin` varchar(11) COLLATE utf8_unicode_ci NOT NULL,		
-                PRIMARY KEY (`id`),		
-                UNIQUE KEY `UNIQ_8244AA3AB28C852F` (`tin`),		
-                UNIQUE KEY `UNIQ_8244AA3A6DB5F457` (`eo_id`),		
-                UNIQUE KEY `UNIQ_8244AA3A99F360CB` (`w9_id`),		
-                UNIQUE KEY `UNIQ_8244AA3ACB6AB30F` (`ach_id`),		
-                UNIQUE KEY `UNIQ_8244AA3A61220EA6` (`creator_id`),		
-                KEY `IDX_8244AA3AA393D2FB` (`state`),		
-                CONSTRAINT `FK_8244AA3A61220EA6` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`),		
-                CONSTRAINT `FK_8244AA3A6DB5F457` FOREIGN KEY (`eo_id`) REFERENCES `eo` (`id`),		
-                CONSTRAINT `FK_8244AA3A99F360CB` FOREIGN KEY (`w9_id`) REFERENCES `documents` (`id`),		
-                CONSTRAINT `FK_8244AA3AA393D2FB` FOREIGN KEY (`state`) REFERENCES `states` (`code`),		
-                CONSTRAINT `FK_8244AA3ACB6AB30F` FOREIGN KEY (`ach_id`) REFERENCES `ach` (`id`)		
-            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
