<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Amc\V2_0\Processors\FeesByZipProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Services\FeeByZipService;
use ValuePad\Core\Amc\Services\FeeService;
use ValuePad\Core\Location\Services\StateService;

class FeesByZipController extends BaseController
{
    /**
     * @var FeeByZipService
     */
    private $feeByZipService;

    /**
     * @var FeeService
     */
    private $feeService;

    /**
     * @param FeeByZipService $feeByZipService
     * @param FeeService $feeService
     */
    public function initialize(FeeByZipService $feeByZipService, FeeService $feeService)
    {
        $this->feeByZipService = $feeByZipService;
        $this->feeService = $feeService;
    }

    /**
     * @param int $amcId
     * @param int $jobTypeId
     * @param string $stateCode
     * @return Response
     */
    public function index($amcId, $jobTypeId, $stateCode)
    {
        $fee = $this->feeService->getByJobTypeId($amcId, $jobTypeId);

        return $this->resource->makeAll(
            $this->feeByZipService->getAllByStateCode($fee->getId(), $stateCode),
            $this->transformer()
        );
    }

    /**
     * @param int $amcId
     * @param int $jobTypeId
     * @param int $stateCode
     * @param FeesByZipProcessor $processor
     * @return Response
     */
    public function sync($amcId, $jobTypeId, $stateCode, FeesByZipProcessor $processor)
    {
        $fee = $this->feeService->getByJobTypeId($amcId, $jobTypeId);

        return $this->resource->makeAll(
            $this->feeByZipService->syncInState($fee->getId(), $stateCode, $processor->createPersistables()),
            $this->transformer()
        );
    }

    /**
     * @param AmcService $amcService
     * @param StateService $stateService
     * @param int $amcId
     * @param int $jobTypeId
     * @param int $stateCode
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, StateService $stateService, $amcId, $jobTypeId, $stateCode)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        if (!$amcService->hasEnabledFeeByJobTypeId($amcId, $jobTypeId)){
            return false;
        }

        return $stateService->exists($stateCode);
    }
}
