<?php
namespace ValuePad\Push\Handlers\Customer\Appraiser;

use ValuePad\Core\Appraiser\Notifications\AbstractAppraiserNotification;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\SettingsService;
use ValuePad\Push\Support\AbstractHandler;
use ValuePad\Push\Support\Call;

abstract class AbstractAppraiserHandler extends AbstractHandler
{
    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * @param AbstractAppraiserNotification $notification
     * @return Call[]
     */
    protected function getCalls($notification)
    {
        $calls = [];

        $settings = $this->settingsService->getAllBySelectedCustomers(
            array_map(
                function(Customer $customer){ return $customer->getId();},
                iterator_to_array($notification->getAppraiser()->getCustomers())
            ));

        foreach ($settings as $single){

            $url = $single->getPushUrl();

            if ($url === null){
                continue ;
            }

            $customer = $single->getCustomer();

            $call = new Call();
            $call->setUrl($url);
            $call->setSecret1($customer->getSecret1());
            $call->setSecret2($customer->getSecret2());
            $call->setUser($customer);

            $calls[] = $call;
        }

        return $calls;
    }

    /**
     * @param AbstractAppraiserNotification $notification
     * @return array
     */
    protected function transform($notification)
    {
        return [
            'type' => 'appraiser',
            'appraiser' => $notification->getAppraiser()->getId()
        ];
    }
}
