<?php
namespace ValuePad\Api\Company\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\ChangeAdditionalStatusProcessor;
use ValuePad\Api\Appraisal\V2_0\Support\AdditionalStatusesTrait;
use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Api\Assignee\V2_0\Processors\ConditionsProcessor;
use ValuePad\Api\Assignee\V2_0\Processors\OrderDeclineProcessor;
use ValuePad\Api\Assignee\V2_0\Processors\OrdersSearchableProcessor;
use ValuePad\Api\Assignee\V2_0\Transformers\TotalsTransformer;
use ValuePad\Api\Company\V2_0\Processors\ReassignOrderProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\AdditionalStatusTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Appraisal\Options\FetchOrdersOptions;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Company\Services\ManagerService;
use ValuePad\Core\Company\Services\PermissionService;
use ValuePad\Core\Shared\Options\PaginationOptions;
use ValuePad\Core\Support\Criteria\Constraint;
use ValuePad\Core\Support\Criteria\Criteria;

class OrdersController extends BaseController
{
    use AdditionalStatusesTrait;
    use CompanyOrdersTrait;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @param OrderService $orderService
     */
    public function initialize(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @param int $managerId
     * @param OrdersSearchableProcessor $processor
     * @return Response
     */
    public function index(OrdersSearchableProcessor $processor, $managerId)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($managerId, $processor){
                $options = new FetchOrdersOptions();
                $options->setPagination(new PaginationOptions($page, $perPage));
                $options->setCriteria($processor->getCriteria());
                $options->setSortables($processor->createSortables());
                return $this->orderService->getAllByAssigneeId($managerId, $options);
            },
            'getTotal' => function() use ($managerId, $processor){
                return $this->orderService->getTotalByAssigneeId($managerId, $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll(
            $this->paginator($adapter),
            $this->transformer(OrderTransformer::class)
        );
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @return Response
     */
    public function show($managerId, $orderId)
    {
        return $this->resource->make(
            $this->orderService->get($orderId),
            $this->transformer(OrderTransformer::class)
        );
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @return Response
     */
    public function accept($managerId, $orderId)
    {
        $this->orderService->accept($orderId);

        return $this->resource->blank();
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @param ConditionsProcessor $processor
     * @return Response
     */
    public function acceptWithConditions($managerId, $orderId, ConditionsProcessor $processor)
    {
        $this->orderService->acceptWithConditions($orderId, $processor->createConditions());

        return $this->resource->blank();
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @param OrderDeclineProcessor $processor
     * @return Response
     */
    public function decline($managerId, $orderId, OrderDeclineProcessor $processor)
    {
        $this->orderService->decline(
            $orderId,
            $processor->getDeclineReason(),
            $processor->getDeclineMessage()
        );

        return $this->resource->blank();
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @param ReassignOrderProcessor $processor
     * @return Response
     */
    public function reassign($managerId, $orderId, ReassignOrderProcessor $processor)
    {
        $this->validateOrderReassignment($managerId, $processor->getAppraiser(), $this->container);

        $this->orderService->reassign($orderId, $processor->getAppraiser());

        return $this->resource->blank();
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @param ChangeAdditionalStatusProcessor $processor
     * @return Response
     */
    public function changeAdditionalStatus(
        ChangeAdditionalStatusProcessor $processor,
        $managerId,
        $orderId
    )
    {
        $this->tryChangeAdditionalStatus(function() use ($orderId, $processor){
            $this->orderService->changeAdditionalStatus(
                $orderId,
                $processor->getAdditionalStatus(),
                $processor->getComment()
            );
        });

        return $this->resource->blank();
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @return Response
     */
    public function listAdditionalStatuses($managerId, $orderId)
    {
        return $this->resource->makeAll(
            $this->orderService->getAllActiveAdditionalStatuses($orderId),
            $this->transformer(AdditionalStatusTransformer::class)
        );
    }

    /**
     * @param OrdersSearchableProcessor $processor
     * @param int $managerId
     * @return Response
     */
    public function totals(OrdersSearchableProcessor $processor, $managerId)
    {
        $criteria = $this->addDefaultCompanyCriteria($managerId, $processor);

        return $this->resource->make([
            'paid' => $this->orderService->getPaidTotalsByAssigneeId($managerId, $criteria, true),
            'unpaid' => $this->orderService->getUnpaidTotalsByAssigneeId($managerId, $criteria, true)
        ], $this->transformer(TotalsTransformer::class));
    }

    /**
     * @param OrdersSearchableProcessor $processor
     * @param int $managerId
     * @return Response
     */
    public function accounting(OrdersSearchableProcessor $processor, $managerId)
    {
        $criteria = $this->addDefaultCompanyCriteria($managerId, $processor);

        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($managerId, $processor, $criteria) {
                $options = new FetchOrdersOptions();
                $options->setPagination(new PaginationOptions($page, $perPage));
                $options->setCriteria($criteria);
                $options->setSortables($processor->createSortables());
                return $this->orderService->getAccountingOrdersByAssigneeId($managerId, $options);
            },
            'getTotal' => function() use ($managerId, $criteria) {
                return $this->orderService->getTotalByAssigneeId($managerId, $criteria);
            }
        ]);

        return $this->resource->makeAll(
            $this->paginator($adapter),
            $this->transformer(OrderTransformer::class)
        );
    }

    /**
     * Add a default company criteria if none were specified
     *
     * @param int $managerId
     * @param OrdersSearchableProcessor $processor
     * @return Criteria[]
     */
    private function addDefaultCompanyCriteria($managerId, OrdersSearchableProcessor $processor)
    {
        $criteria = $processor->getCriteria();

        if (! $processor->get('filter.company')) {
            $permissionService = $this->container->make(PermissionService::class);
            $companies = $permissionService->getAllCompaniesByManagerId($managerId);
            $criteria[] = new Criteria('company', new Constraint(Constraint::EQUAL), $companies[0]);
        }

        return $criteria;
    }

    /**
     * @param ManagerService $managerService
     * @param int $managerId
     * @param int  $orderId
     * @return bool
     */
    public static function verifyAction(ManagerService $managerService, $managerId, $orderId = null)
    {
        if (!$managerService->exists($managerId)){
            return false;
        }

        if ($orderId === null){
            return true;
        }

        return $managerService->hasOrder($managerId, $orderId);
    }
}
