<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;
use ValuePad\Api\Assignee\V2_0\Transformers\TotalsTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Services\OrderService;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Api\Assignee\V2_0\Processors\OrdersSearchableProcessor;
use ValuePad\Api\Customer\V2_0\Processors\OrdersProcessor;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Appraisal\Options\FetchOrdersOptions;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class AppraiserOrdersController extends BaseController
{
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @param OrderService $orderService
     */
    public function initialize(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @param int $customerId
     * @param int $appraiserId
     * @param OrdersProcessor $processor
     * @return Response
     */
    public function store($customerId, $appraiserId, OrdersProcessor $processor)
    {
        return $this->resource->make(
            $this->orderService->create($customerId, $appraiserId, $processor->createPersistable()),
            $this->transformer()
        );
    }

    /**
     * @param int $customerId
     * @param int $appraiserId
     * @param OrdersSearchableProcessor $processor
     * @return Response
     */
    public function index($customerId, $appraiserId, OrdersSearchableProcessor $processor)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($customerId, $appraiserId, $processor){
                $options = new FetchOrdersOptions();
                $options->setPagination(new PaginationOptions($page, $perPage));
                $options->setCriteria($processor->getCriteria());
                $options->setSortables($processor->createSortables());
                return $this->orderService->getAllByCustomerAndAssigneeIds($customerId, $appraiserId, $options);
            },
            'getTotal' => function() use ($customerId, $appraiserId, $processor){
                return $this->orderService->getTotalByCustomerAndAssigneeIds($customerId, $appraiserId, $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll(
            $this->paginator($adapter),
            $this->transformer(OrderTransformer::class)
        );
    }


    /**
     * @param int $customerId
     * @param int $appraiserId
     * @return Response
     */
    public function totals($customerId, $appraiserId)
    {
        return $this->resource->make([
            'paid' => $this->orderService->getPaidTotalsByCustomerAndAssigneeIds($customerId, $appraiserId),
            'unpaid' => $this->orderService->getUnpaidTotalsByCustomerAndAssigneeIds($customerId, $appraiserId)
        ], $this->transformer(TotalsTransformer::class));
    }


    /**
     * @param CustomerService $customerService
     * @param AppraiserService $appraiserService
     * @param int $customerId
     * @param int $appraiserId
     * @return bool
     */
    public static function verifyAction(
        CustomerService $customerService,
        AppraiserService $appraiserService,
        $customerId,
        $appraiserId
    )
    {
        if (!$customerService->exists($customerId)){
            return false;
        }

        return $appraiserService->exists($appraiserId);
    }
}
