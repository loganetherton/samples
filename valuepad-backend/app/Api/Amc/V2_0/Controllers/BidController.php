<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\BidsProcessor;
use ValuePad\Api\Appraisal\V2_0\Transformers\BidTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Appraisal\Services\BidService;
use ValuePad\Core\Appraisal\Services\OrderService;

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
     * @param int $amcId
     * @param int $orderId
     * @param BidsProcessor $processor
     * @return Response
     */
    public function store($amcId, $orderId, BidsProcessor $processor)
    {
        return $this->resource->make(
            $this->bidService->create($orderId, $processor->createPersistable()),
            $this->transformer(BidTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $orderId
     * @return Response
     */
    public function show($amcId, $orderId)
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
     * @param AmcService $amcService
     * @param int $amcId
     * @param int $orderId
     * @return bool
     */
    public static function verifyAction(
        AmcService $amcService,
        $amcId,
        $orderId = null
    )
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
