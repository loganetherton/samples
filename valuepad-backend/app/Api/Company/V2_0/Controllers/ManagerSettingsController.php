<?php
namespace ValuePad\Api\Company\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Assignee\V2_0\Processors\SettingsProcessor;
use ValuePad\Api\Assignee\V2_0\Transformers\NotificationSubscriptionsTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Assignee\Services\NotificationSubscriptionService;
use ValuePad\Core\Company\Services\ManagerService;

class ManagerSettingsController extends BaseController
{
    /**
     * @var NotificationSubscriptionService
     */
    private $notificationSubscriptionService;

    public function initialize(NotificationSubscriptionService $notificationSubscriptionService)
    {
        $this->notificationSubscriptionService = $notificationSubscriptionService;
    }

    /**
     * @param int $managerId
     * @return Response
     */
    public function show($managerId)
    {
        return $this->resource->make([
            'notifications' => $this->transformer(NotificationSubscriptionsTransformer::class)
                ->transform($this->notificationSubscriptionService->getAll($managerId))
        ]);
    }

    /**
     * @param int $managerId
     * @param SettingsProcessor $processor
     * @return Response
     */
    public function update($managerId, SettingsProcessor $processor)
    {
        if ($subscriptions = $processor->createNotificationSubscriptionPersistables()){

            $this->notificationSubscriptionService
                ->updateBySelectedCustomers($managerId, $subscriptions);
        }

        return $this->resource->blank();
    }

    /**
     * @param ManagerService $managerService
     * @param int $managerId
     * @return bool
     */
    public static function verifyAction(ManagerService $managerService, $managerId)
    {
        return $managerService->exists($managerId);
    }
}
