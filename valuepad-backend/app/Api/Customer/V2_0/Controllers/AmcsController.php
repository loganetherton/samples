<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;
use Ascope\Libraries\Verifier\Action;
use Illuminate\Http\Response;
use ValuePad\Api\Amc\V2_0\Transformers\AmcTransformer;
use ValuePad\Api\Customer\V2_0\Processors\OrdersProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Amc\Options\FetchAmcsOptions;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class AmcsController extends BaseController
{
    /**
     * @var AmcService
     */
    private $amcService;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @param AmcService $amcService
     * @param OrderService $orderService
     */
    public function initialize(AmcService $amcService, OrderService $orderService)
    {
        $this->amcService = $amcService;
        $this->orderService = $orderService;
    }

    /**
     * @param int $customerId
     * @return Response
     */
    public function index($customerId)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($customerId){
                $options = new FetchAmcsOptions();
                $options->setPagination(new PaginationOptions($page, $perPage));
                return $this->amcService->getAllByCustomerId($customerId, $options);
            },
            'getTotal' => function() use ($customerId){
                return $this->amcService->getTotalByCustomerId($customerId);
            }
        ]);


        return $this->resource->makeAll(
            $this->paginator($adapter),
            $this->transformer(AmcTransformer::class)
        );
    }

    /**
     * @param int $customerId
     * @param int $amcId
     * @param OrdersProcessor $processor
     * @return Response
     */
    public function storeOrder($customerId, $amcId, OrdersProcessor $processor)
    {
        return $this->resource->make(
            $this->orderService->create($customerId, $amcId, $processor->createPersistable()),
            $this->transformer()
        );
    }

    /**
     * @param Action $action
     * @param AmcService $amcService
     * @param CustomerService $customerService
     * @param int $customerId
     * @param int $amcId
     * @return bool
     */
    public static function verifyAction(
        Action $action,
        AmcService $amcService,
        CustomerService
        $customerService,
        $customerId,
        $amcId = null
    )
    {
        if (!$customerService->exists($customerId)){
            return false;
        }

        if ($amcId === null){
            return true;
        }

        if ($action->is('storeOrder')){
            return $amcService->exists($amcId);
        }

        return $customerService->isRelatedWithAmc($customerId, $amcId);
    }
}
