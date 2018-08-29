<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Appraiser\V2_0\Processors\AppraisersSearchableProcessor;
use ValuePad\Api\Appraiser\V2_0\Transformers\AppraiserTransformer;
use ValuePad\Api\Assignee\V2_0\Transformers\NotificationSubscriptionsTransformer;
use ValuePad\Api\Assignee\V2_0\Processors\LogsSearchableProcessor;
use ValuePad\Api\Assignee\V2_0\Transformers\LogTransformer;
use ValuePad\Api\Customer\V2_0\Transformers\AppraiserAchTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Appraiser\Options\FetchAppraisersOptions;
use ValuePad\Core\Appraiser\Services\AchService;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Assignee\Services\NotificationSubscriptionService;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Log\Options\FetchLogsOptions;
use ValuePad\Core\Log\Services\LogService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class AppraisersController extends BaseController
{
	/**
	 * @var AppraiserService
	 */
	private $appraiserService;

	/**
	 * @var AchService
	 */
	private $achService;

	/**
	 * @param AppraiserService $appraiserService
	 * @param AchService $achService
	 */
	public function initialize(AppraiserService $appraiserService, AchService $achService)
	{
		$this->appraiserService = $appraiserService;
		$this->achService = $achService;
	}

	/**
	 * @param int $customerId
	 * @param AppraisersSearchableProcessor $processor
	 * @return Response
	 */
	public function index($customerId, AppraisersSearchableProcessor $processor)
	{
		$adapter = new DefaultPaginatorAdapter([
			'getAll' => function($page, $perPage) use ($processor, $customerId){
				$options = new FetchAppraisersOptions();
				$options->setPagination(new PaginationOptions($page, $perPage));
				$options->setCriteria($processor->getCriteria());
				return $this->appraiserService->getAllByCustomerId($customerId, $options);
			},
			'getTotal' => function() use ($processor, $customerId){
				return $this->appraiserService->getTotalByCustomerId($customerId, $processor->getCriteria());
			}
		]);

		return $this->resource->makeAll($this->paginator($adapter), $this->transformer(AppraiserTransformer::class));
	}

	/**
	 * @param int $customerId
	 * @param int $appraiserId
	 * @return Response
	 */
	public function show($customerId, $appraiserId)
	{
		return $this->resource->make(
			$this->appraiserService->get($appraiserId),
			$this->transformer(AppraiserTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $appraiserId
	 * @return Response
	 */
	public function ach($customerId, $appraiserId)
	{
		return $this->resource->make(
			$this->achService->getExistingOrEmpty($appraiserId),
			$this->transformer(AppraiserAchTransformer::class)
		);
	}

    /**
     * @param int $customerId
     * @param int $appraiserId
     * @param LogsSearchableProcessor $processor
     * @return Response
     */
    public function logs($customerId, $appraiserId, LogsSearchableProcessor $processor)
    {
        /**
         * @var LogService $logsService
         */
        $logsService = $this->container->make(LogService::class);

        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($customerId, $appraiserId, $processor, $logsService){
                $options = new FetchLogsOptions();
                $options->setCriteria($processor->getCriteria());
                $options->setSortables($processor->createSortables());
                $options->setPagination(new PaginationOptions($page, $perPage));

                return $logsService->getAllByCustomerAndAssigneeIds($customerId, $appraiserId, $options);
            },
            'getTotal' => function() use ($customerId, $appraiserId, $processor, $logsService){
                return $logsService->getTotalByCustomerAndAssigneeId($customerId, $appraiserId, $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll($this->paginator($adapter), $this->transformer(LogTransformer::class));
    }

    /**
     * @param int $customerId
     * @param int $appraiserId
     * @return Response
     */
    public function settings($customerId, $appraiserId)
    {
        /**
         * @var NotificationSubscriptionService $subscriptionService
         */
        $subscriptionService = $this->container->make(NotificationSubscriptionService::class);

        $subscription = $subscriptionService->getByCustomerId($appraiserId, $customerId);

        return $this->resource->make([
            'notifications' => $this->transformer(NotificationSubscriptionsTransformer::class)
                ->transform([$subscription])
        ]);
    }

	/**
	 * @param CustomerService $customerService
	 * @param int $customerId
	 * @param int $appraiserId
	 * @return bool
	 */
	public static function verifyAction(
		CustomerService $customerService,
		$customerId,
		$appraiserId = null
	)
	{
		if (!$customerService->exists($customerId)){
			return false;
		}

		if ($appraiserId === null){
			return true;
		}

		return $customerService->isRelatedWithAppraiser($customerId, $appraiserId);
	}
}
