<?php
namespace ValuePad\Api\Company\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Api\Assignee\V2_0\Processors\OrdersSearchableProcessor;
use ValuePad\Api\Assignee\V2_0\Transformers\CountersTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Appraisal\Enums\Queue;
use ValuePad\Core\Appraisal\Options\FetchOrdersOptions;
use ValuePad\Core\Appraisal\Services\QueueService;
use ValuePad\Core\Company\Services\ManagerService;
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
     * @param int $managerId
     * @return Response
     */
    public function counters($managerId)
    {
        return $this->resource->make(
            $this->queueService->getCountersByAssigneeId($managerId),
            $this->transformer(CountersTransformer::class)
        );
    }

    /**
     * @param OrdersSearchableProcessor $processor
     * @param int $managerId
     * @param string $name
     * @return Response
     */
    public function index(OrdersSearchableProcessor $processor, $managerId, $name)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($managerId, $name, $processor){
                $options = new FetchOrdersOptions();
                $options->setPagination(new PaginationOptions($page, $perPage));
                $options->setCriteria($processor->getCriteria());
                $options->setSortables($processor->createSortables());
                return $this->queueService->getAllByAssigneeId($managerId, new Queue($name), $options);
            },
            'getTotal' => function() use ($managerId, $name, $processor){
                return $this->queueService->getTotalByAssigneeId($managerId, new Queue($name), $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll(
            $this->paginator($adapter),
            $this->transformer(OrderTransformer::class)
        );
    }

    /**
     * @param ManagerService $managerService
     * @param int $managerId
     * @param string $name
     * @return bool
     */
    public static function verifyAction(ManagerService $managerService, $managerId, $name = null)
    {
        if (!$managerService->exists($managerId)){
            return false;
        }

        if ($name === null){
            return true;
        }

        return Queue::has($name);
    }
}
