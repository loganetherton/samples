<?php
namespace ValuePad\Api\Assignee\V2_0\Protectors;

use ValuePad\Api\Shared\Protectors\AuthProtector;
use Illuminate\Http\Request;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Session\Entities\Session;

abstract class AbstractCustomerProtector extends AuthProtector
{
    public function grants()
    {
        if (!parent::grants()){
            return false;
        }

        /**
         * @var Request $request
         */
        $request = $this->container->make('request');

        $assigneeId = (int) array_values($request->route()->parameters())[0];

        /**
         * @var Session $session
         */
        $session = $this->container->make(Session::class);

        $customer = $session->getUser();

        if (!$customer instanceof Customer){
            return false;
        }

        return $this->isRelated($customer->getId(), $assigneeId);
    }

    /**
     * @param int $customerId
     * @param int $assigneeId
     * @return bool
     */
    abstract function isRelated($customerId, $assigneeId);
}
