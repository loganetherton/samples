<?php
namespace ValuePad\Api\Company\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\BidsProcessor;
use ValuePad\Api\Appraisal\V2_0\Transformers\BidTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Services\BidService;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Company\Services\ManagerService;

class BidController extends BaseController
{
    /**
     * @var BidService
     */
    private $bidService;

    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @param BidService $bidService
     * @param OrderService $orderService
     */
    public function initialize(BidService $bidService, OrderService $orderService)
    {
        $this->bidService = $bidService;
        $this->orderService = $orderService;
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @param BidsProcessor $processor
     * @return Response
     */
    public function store($managerId, $orderId, BidsProcessor $processor)
    {
        return $this->resource->make(
            $this->bidService->create($orderId, $processor->createPersistable()),
            $this->transformer(BidTransformer::class)
        );
    }

    /**
     * @param int $managerId
     * @param int $orderId
     * @return Response
     */
    public function show($managerId, $orderId)
    {
        if (!$this->orderService->hasBid($orderId)){
            return $this->resource->error()->notFound();
        }

        return $this->resource->make(
            $this->bidService->get($orderId),
            $this->transformer(BidTransformer::class)
        );
    }

    /**
     * @param ManagerService $managerService
     * @param int $managerId
     * @param int $orderId
     * @return bool
     */
    public static function verifyAction(
        ManagerService $managerService,
        $managerId,
        $orderId
    )
    {
        if (!$managerService->exists($managerId)){
            return false;
        }

        return $managerService->hasOrder($managerId, $orderId);
    }
}
