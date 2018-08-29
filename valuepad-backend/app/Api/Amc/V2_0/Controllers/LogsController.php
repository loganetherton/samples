<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Ascope\Libraries\Verifier\Action;
use Illuminate\Http\Response;
use ValuePad\Api\Assignee\V2_0\Transformers\LogTransformer;
use ValuePad\Api\Assignee\V2_0\Processors\LogsSearchableProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Log\Options\FetchLogsOptions;
use ValuePad\Core\Log\Services\LogService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class LogsController extends BaseController
{
    /**
     * @var LogService
     */
    private $logService;

    /**
     * @param LogService $logService
     */
    public function initialize(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * @param int $amcId
     * @param LogsSearchableProcessor $processor
     * @return Response
     */
    public function index($amcId, LogsSearchableProcessor $processor)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($amcId, $processor){
                $options = new FetchLogsOptions();
                $options->setCriteria($processor->getCriteria());
                $options->setSortables($processor->createSortables());
                $options->setPagination(new PaginationOptions($page, $perPage));

                return $this->logService->getAllByAssigneeId($amcId, $options);
            },
            'getTotal' => function() use ($amcId, $processor){
                return $this->logService->getTotalByAssigneeId($amcId, $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll($this->paginator($adapter), $this->transformer(LogTransformer::class));
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @param LogsSearchableProcessor $processor
     * @return Response
     */
    public function indexByOrder($amcId, $orderId, LogsSearchableProcessor $processor)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($orderId, $processor){
                $options = new FetchLogsOptions();
                $options->setCriteria($processor->getCriteria());
                $options->setPagination(new PaginationOptions($page, $perPage));
                $options->setSortables($processor->createSortables());

                return $this->logService->getAllByOrderId($orderId, $options);
            },
            'getTotal' => function() use ($orderId, $processor){
                return $this->logService->getTotalByOrderId($orderId, $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll($this->paginator($adapter), $this->transformer(LogTransformer::class));
    }

    /**
     * @param int $amcId
     * @param int $logId
     * @return Response
     */
    public function show($amcId, $logId)
    {
        return $this->resource->make($this->logService->get($logId), $this->transformer(LogTransformer::class));
    }

    /**
     * @param Action $action
     * @param AmcService $amcService
     * @param int $amcId
     * @param int $orderIdOrLogId
     * @return bool
     */
    public static function verifyAction(Action $action, AmcService $amcService, $amcId, $orderIdOrLogId = null)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        if ($orderIdOrLogId === null){
            return true;
        }

        if ($action->is('indexByOrder')){
            return $amcService->hasOrder($amcId, $orderIdOrLogId);
        }

        return $amcService->hasLog($amcId, $orderIdOrLogId);
    }
}
