<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Api\Assignee\V2_0\Processors\OrdersSearchableProcessor;
use ValuePad\Api\Assignee\V2_0\Transformers\CountersTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraisal\Enums\Queue;
use ValuePad\Core\Appraisal\Options\FetchOrdersOptions;
use ValuePad\Core\Appraisal\Services\QueueService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class QueuesController extends BaseController
{
    /**
     * @var QueueService
     */
    private $queueService;

    /**
     * @param QueueService $queueService
     */
    public function initialize(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * @param OrdersSearchableProcessor $processor
     * @param int $amcId
     * @param string $name
     * @return Response
     */
    public function index(OrdersSearchableProcessor $processor, $amcId, $name)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($amcId, $name, $processor){
                $options = new FetchOrdersOptions();
                $options->setPagination(new PaginationOptions($page, $perPage));
                $options->setCriteria($processor->getCriteria());
                $options->setSortables($processor->createSortables());
                return $this->queueService->getAllByAssigneeId($amcId, new Queue($name), $options);
            },
            'getTotal' => function() use ($amcId, $name, $processor){
                return $this->queueService->getTotalByAssigneeId($amcId, new Queue($name), $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll(
            $this->paginator($adapter),
            $this->transformer(OrderTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @return Response
     */
    public function counters($amcId)
    {
        return $this->resource->make(
            $this->queueService->getCountersByAssigneeId($amcId),
            $this->transformer(CountersTransformer::class)
        );
    }

    /**
     * @param AmcService $amcService
     * @param int $amcId
     * @param string $name
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, $amcId, $name = null)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        if ($name === null){
            return true;
        }

        return Queue::has($name);
    }
}
