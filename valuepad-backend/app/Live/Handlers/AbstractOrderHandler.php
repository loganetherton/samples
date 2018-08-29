<?php
namespace ValuePad\Live\Handlers;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Services\PermissionService;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\User\Entities\User;
use ValuePad\Live\Support\AbstractHandler;
use ValuePad\Live\Support\Channel;
use RuntimeException;

abstract class AbstractOrderHandler extends AbstractHandler
{
    /**
     * @return string
     */
    protected function getType()
    {
        return 'order';
    }

    /**
     * @param object $notification
     * @return Channel[]
     */
    protected function getChannels($notification)
    {
        if (!$notification instanceof AbstractNotification){
            throw new RuntimeException('Unable to determine channels for the "'.get_class($notification).'" notification.');
        }

        $order = $notification->getOrder();

        return $this->buildChannels($order->getAssignee(),  $order->getCustomer());
    }

    /**
     * @param User $assignee
     * @param Customer $customer
     * @return Channel[]
     */
    protected function buildChannels(User $assignee, Customer $customer)
    {
        $channels = [];

        $channels[] = new Channel($assignee);

        if ($assignee instanceof Appraiser){
            $channels[] = new Channel($customer, $assignee);

            /**
             * @var PermissionService $permissionService
             */
            $permissionService = $this->container->make(PermissionService::class);

            $managers = $permissionService->getAllManagersByAppraiserId($assignee->getId());

            foreach ($managers as $manager){
                $channels[] = new Channel($manager);
            }
        }

        return $channels;
    }
}
