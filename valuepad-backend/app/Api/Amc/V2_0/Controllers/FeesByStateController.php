<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Amc\V2_0\Processors\FeesByStateBulkProcessor;
use ValuePad\Api\Amc\V2_0\Processors\FeesByStateProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Services\FeeByCountyService;
use ValuePad\Core\Amc\Services\FeeByStateService;
use ValuePad\Core\Amc\Services\FeeByZipService;
use ValuePad\Core\Amc\Services\FeeService;

class FeesByStateController extends BaseController
{
    /**
     * @var FeeByStateService
     */
    private $feeByStateService;

    /**
     * @var FeeService
     */
    private $feeService;

    /**
     * @var FeeByCountyService
     */
    private $feeByCountyService;

    /**
     * @var FeeByZipService
     */
    private $feeByZipService;

    /**
     * @param FeeByStateService $feeByStateService
     * @param FeeService $feeService
     * @param FeeByCountyService $feeByCountyService,
     * @param FeeByZipService $feeByZipService
     */
    public function initialize(
        FeeByStateService $feeByStateService,
        FeeService $feeService,
        FeeByCountyService $feeByCountyService,
        FeeByZipService $feeByZipService
    )
    {
        $this->feeByStateService = $feeByStateService;
        $this->feeService = $feeService;
        $this->feeByCountyService = $feeByCountyService;
        $this->feeByZipService = $feeByZipService;
    }

    /**
     * @param int $amcId
     * @param int $jobTypeId
     * @return Response
     */
    public function index($amcId, $jobTypeId)
    {
        $fee = $this->feeService->getByJobTypeId($amcId, $jobTypeId);

        return $this->resource->makeAll(
            $this->feeByStateService->getAll($fee->getId()),
            $this->transformer()
        );
    }

    /**
     * @param int $amcId
     * @param int $jobTypeId
     * @param FeesByStateBulkProcessor $processor
     * @return Response
     */
    public function sync($amcId, $jobTypeId, FeesByStateBulkProcessor $processor)
    {
        $fee = $this->feeService->getByJobTypeId($amcId, $jobTypeId);

        $result = $this->feeByStateService->sync($fee->getId(), $processor->createPersistables());

        foreach ($processor->getApplyStateAmountToAllCounties() as $state => $flag){
            if (!$flag){
                continue ;
            }

            $feeByState = $this->feeByStateService->getByStateCode($fee->getId(), $state);
            $this->feeByCountyService->applyStateAmountToAllInState($feeByState->getId());
        }

        foreach ($processor->getApplyStateAmountToAllZips() as $state => $flag){
            if (!$flag){
                continue ;
            }

            $feeByState = $this->feeByStateService->getByStateCode($fee->getId(), $state);
            $this->feeByZipService->applyStateAmountToAllInState($feeByState->getId());
        }

        return $this->resource->makeAll($result, $this->transformer());
    }

    /**
     * @param int $amcId
     * @param int $jobTypeId
     * @param string $state
     * @param FeesByStateProcessor $processor
     * @return Response
     */
    public function update($amcId, $jobTypeId, $state, FeesByStateProcessor $processor)
    {
        $fee = $this->feeService->getByJobTypeId($amcId, $jobTypeId);

        $feeByState = $this->feeByStateService->getByStateCode($fee->getId(), $state);

        $this->feeByStateService->update(
            $feeByState->getId(),
            $processor->createPersistable(),
            $processor->schedulePropertiesToClear()
        );

        if ($processor->getApplyStateAmountToAllCounties()){
            $this->feeByCountyService->applyStateAmountToAllInState($feeByState->getId());
        }

        if ($processor->getApplyStateAmountToAllZips()){
            $this->feeByZipService->applyStateAmountToAllInState($feeByState->getId());
        }

        return $this->resource->blank();
    }

    /**
     * @param AmcService $amcService
     * @param FeeService $feeService
     * @param int $amcId
     * @param int $jobTypeId
     * @param string $stateCode
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, FeeService $feeService, $amcId, $jobTypeId, $stateCode = null)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }


        if (!$amcService->hasEnabledFeeByJobTypeId($amcId, $jobTypeId)){
            return false;
        }

        if ($stateCode === null){
            return true;
        }

        $fee = $feeService->getByJobTypeId($amcId, $jobTypeId);

        return $feeService->hasFeeByStateByStateCode($fee->getId(), $stateCode);
    }
}
