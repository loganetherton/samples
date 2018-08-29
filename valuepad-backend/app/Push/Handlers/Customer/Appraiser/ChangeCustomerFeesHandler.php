<?php
namespace ValuePad\Push\Handlers\Customer\Appraiser;

use ValuePad\Core\Appraiser\Notifications\ChangeCustomerFeesNotification;
use ValuePad\Push\Support\AbstractHandler;
use ValuePad\Push\Support\Call;

class ChangeCustomerFeesHandler extends AbstractHandler
{
    /**
     * @param ChangeCustomerFeesNotification $notification
     * @return Call[]
     */
    protected function getCalls($notification)
    {
        $customer = $notification->getCustomer();

        $url = $customer->getSettings()->getPushUrl();

        if (!$url){
            return [];
        }

        $call = new Call();
        $call->setUrl($url);
        $call->setSecret1($customer->getSecret1());
        $call->setSecret2($customer->getSecret2());
        $call->setUser($customer);

        return [$call];

    }

    /**
     * @param ChangeCustomerFeesNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        return [
            'type' => 'appraiser',
            'event' => 'change-customer-fees',
            'appraiser' => $notification->getAppraiser()->getId()
        ];
    }
}
