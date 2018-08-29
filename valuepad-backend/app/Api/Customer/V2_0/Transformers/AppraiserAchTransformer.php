<?php
namespace ValuePad\Api\Customer\V2_0\Transformers;

use Illuminate\Container\Container;
use Illuminate\Encryption\Encrypter;
use ValuePad\Api\Appraiser\V2_0\Transformers\AchTransformer;
use ValuePad\Core\Appraiser\Entities\Ach;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Session\Entities\Session;

class AppraiserAchTransformer extends AchTransformer
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Ach $item
     * @return array
     */
    public function transform($item)
    {
        /**
         * @var Session $session
         */
        $session = $this->container->make(Session::class);

        /**
         * @var Customer $customer
         */
        $customer = $session->getUser();

        $data = parent::transform($item);

        $data['encryptedAccountNumber'] = null;

        $encrypter = new Encrypter(md5($customer->getSecret1().$customer->getSecret2()), 'AES-256-CBC');

        if ($item->getAccountNumber() !== null){

            $data['encryptedAccountNumber'] = $encrypter->encrypt($item->getAccountNumber());
        }

        $data['encryptedRouting'] = null;

        if ($item->getRouting() !== null){
            $data['encryptedRouting'] = $encrypter->encrypt($item->getRouting());
        }

        return $data;
    }
}
