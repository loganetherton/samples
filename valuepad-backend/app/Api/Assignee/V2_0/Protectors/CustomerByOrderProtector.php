<?php
namespace ValuePad\Api\Assignee\V2_0\Protectors;
use Illuminate\Http\Request;
use ValuePad\Api\Shared\Protectors\AuthProtector;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Session\Entities\Session;

class CustomerByOrderProtector extends AuthProtector
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


        $orderId = (int) array_values($request->route()->parameters())[1];

        /**
         * @var OrderService $orderService
         */
        $orderService =  $this->container->make(OrderService::class);

        $order = $orderService->get($orderId);

        if (!$order){
            return true;
        }

        /**
         * @var Session $session
         */
        $session = $this->container->make(Session::class);

        return $session->getUser()->getId() === $order->getCustomer()->getId();
    }
}
