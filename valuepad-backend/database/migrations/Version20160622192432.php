<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use ValuePad\Core\User\Entities\System;
use ValuePad\Core\User\Interfaces\PasswordEncryptorInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160622192432 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = app(EntityManagerInterface::class);

        $system = new System();

        $system->setEmail('support@appraisalscope.com');

        /**
         * @var PasswordEncryptorInterface $encryptor
         */
        $encryptor = app()->make(PasswordEncryptorInterface::class);

        $system->setPassword($encryptor->encrypt(uniqid('vp_')));
        $system->setUsername('valuepad');
        $system->setName('ValuePad');

        $entityManager->persist($system);
        $entityManager->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
