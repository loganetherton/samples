<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Amc\V2_0\Processors\FeesProcessor;
use ValuePad\Api\Assignee\V2_0\Transformers\TotalTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Services\FeeService;

class FeesController extends BaseController
{
    /**
     * @var FeeService
     */
    private $feeService;

    /**
     * @param FeeService $feeService
     */
    public function initialize(FeeService $feeService)
    {
        $this->feeService = $feeService;
    }

    /**
     * @param int $amcId
     * @return Response
     */
    public function index($amcId)
    {
        return $this->resource->makeAll($this->feeService->getAllEnabled($amcId),  $this->transformer());
    }

    /**
     * @param int $amcId
     * @param FeesProcessor $processor
     * @return Response
     */
    public function sync($amcId, FeesProcessor $processor)
    {
        return $this->resource->makeAll(
            $this->feeService->sync($amcId, $processor->createPersistables()),
            $this->transformer()
        );
    }

    /**
     * @param int @amcId
     * @return Response
     */
    public function totals($amcId)
    {
        return $this->resource->makeAll(
            $this->feeService->getTotals($amcId),
            $this->transformer(TotalTransformer::class)
        );
    }

    /**
     * @param AmcService $amcService
     * @param int $amcId
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, $amcId)
    {
        return $amcService->exists($amcId);
    }
}
