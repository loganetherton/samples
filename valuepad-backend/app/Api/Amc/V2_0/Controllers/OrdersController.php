<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\ChangeAdditionalStatusProcessor;
use ValuePad\Api\Appraisal\V2_0\Support\AdditionalStatusesTrait;
use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Api\Assignee\V2_0\Transformers\TotalsTransformer;
use ValuePad\Api\Assignee\V2_0\Processors\OrderDeclineProcessor;
use ValuePad\Api\Assignee\V2_0\Processors\ConditionsProcessor;
use ValuePad\Api\Assignee\V2_0\Processors\OrdersSearchableProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\AdditionalStatusTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraisal\Options\FetchOrdersOptions;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class OrdersController extends BaseController
{
    use AdditionalStatusesTrait;

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
     * @param int $amcId
     * @param OrdersSearchableProcessor $processor
     * @return Response
     */
    public function index(OrdersSearchableProcessor $processor, $amcId)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($amcId, $processor){
                $options = new FetchOrdersOptions();
                $options->setPagination(new PaginationOptions($page, $perPage));
                $options->setCriteria($processor->getCriteria());
                $options->setSortables($processor->createSortables());
                return $this->orderService->getAllByAssigneeId($amcId, $options);
            },
            'getTotal' => function() use ($amcId, $processor){
                return $this->orderService->getTotalByAssigneeId($amcId, $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll(
            $this->paginator($adapter),
            $this->transformer(OrderTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function show($amcId, $orderId)
    {
        return $this->resource->make(
            $this->orderService->get($orderId),
            $this->transformer(OrderTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @param ChangeAdditionalStatusProcessor $processor
     * @return Response
     */
    public function changeAdditionalStatus(
        ChangeAdditionalStatusProcessor $processor,
        $amcId,
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
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function accept($amcId, $orderId)
    {
        $this->orderService->accept($orderId);

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @param ConditionsProcessor $processor
     * @return Response
     */
    public function acceptWithConditions($amcId, $orderId, ConditionsProcessor $processor)
    {
        $this->orderService->acceptWithConditions($orderId, $processor->createConditions());

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @param OrderDeclineProcessor $processor
     * @return Response
     */
    public function decline($amcId, $orderId, OrderDeclineProcessor $processor)
    {
        $this->orderService->decline(
            $orderId,
            $processor->getDeclineReason(),
            $processor->getDeclineMessage()
        );

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @return Response
     */
    public function totals($amcId)
    {
        return $this->resource->make([
            'paid' => $this->orderService->getPaidTotalsByAssigneeId($amcId),
            'unpaid' => $this->orderService->getUnpaidTotalsByAssigneeId($amcId)
        ], $this->transformer(TotalsTransformer::class));
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function listAdditionalStatuses($amcId, $orderId)
    {
        return $this->resource->makeAll(
            $this->orderService->getAllActiveAdditionalStatuses($orderId),
            $this->transformer(AdditionalStatusTransformer::class)
        );
    }

    /**
     * @param $amcId
     * @param $orderId
     * @return Response
     */
    public function destroy($amcId, $orderId)
    {
        $this->orderService->delete($orderId);

        return $this->resource->blank();
    }

    /**
     * @param AmcService $amcService
     * @param int $amcId
     * @param int $orderId
     * @return bool
     */
    public static function verifyAction(AmcService $amcService,  $amcId,  $orderId = null)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        if ($orderId === null){
            return true;
        }

        return $amcService->hasOrder($amcId, $orderId);
    }
}
