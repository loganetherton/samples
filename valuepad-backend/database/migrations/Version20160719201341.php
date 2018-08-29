<?php

namespace ValuePad\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use net\authorize\api\contract\v1\GetCustomerPaymentProfileRequest;
use net\authorize\api\contract\v1\GetCustomerPaymentProfileResponse;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\controller\GetCustomerPaymentProfileController;
use ValuePad\Core\Payment\Entities\ProfileReference;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160719201341 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $config = app()->make('config')->get('app.authorize_net');

        /**
         * @var EntityManagerInterface $em
         */
        $em = app()->make(EntityManagerInterface::class);

        /**
         * @var ProfileReference[] $references
         */
        $references = $em->getRepository(ProfileReference::class)->findAll();


        foreach ($references as $reference){

            $request = new GetCustomerPaymentProfileRequest();
            $request->setCustomerProfileId($reference->getProfileId());
            $request->setCustomerPaymentProfileId($reference->getCreditCardProfileId());

            $merchantAuthentication = new MerchantAuthenticationType();
            $merchantAuthentication->setName($config['login_id']);
            $merchantAuthentication->setTransactionKey($config['transaction_key']);
            $request->setMerchantAuthentication($merchantAuthentication);

            $controller = new GetCustomerPaymentProfileController($request);

            /**
             * @var GetCustomerPaymentProfileResponse $response
             */
            $response = $controller->executeWithApiResponse($config['environment']);

            $ccn = $response->getPaymentProfile()->getPayment()->getCreditCard()->getCardNumber();

            $reference->setMaskedCreditCardNumber(substr($ccn, -4, 4));
        }

        $em->flush();
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
