<?php
namespace ValuePad\Push\Handlers\Customer\Shared;

use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\Shared\Notifications\AvailabilityPerCustomerNotification;
use ValuePad\Push\Support\AbstractHandler;
use ValuePad\Push\Support\Call;

class AvailabilityPerCustomerHandler extends AbstractHandler
{
    /**
     * @param AvailabilityPerCustomerNotification $notification
     * @return Call[]
     */
    protected function getCalls($notification)
    {
        $customer = $notification->getAvailability()->getCustomer();

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
     * @param AvailabilityPerCustomerNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        $data = ['event' => 'update'];

        $type = 'appraiser';

        if ($notification->getAvailability()->getUser() instanceof Manager) {
            $type = 'manager';
        }

        $data['type'] = $type;
        $data[$type] = $notification->getAvailability()->getUser()->getId();

        return $data;
    }
}
