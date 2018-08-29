<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Amc\V2_0\Processors\FeesByStateBulkProcessor;
use ValuePad\Api\Amc\V2_0\Processors\FeesByStateProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Services\CustomerFeeByCountyService;
use ValuePad\Core\Amc\Services\CustomerFeeByStateService;
use ValuePad\Core\Amc\Services\CustomerFeeByZipService;
use ValuePad\Core\Amc\Services\CustomerFeeService;

class CustomerFeesByStateController extends BaseController
{
    /**
     * @var CustomerFeeByStateService
     */
    private $customerFeeByStateService;

    /**
     * @var CustomerFeeByCountyService
     */
    private $feeByCountyService;

    /**
     * @var CustomerFeeByZipService
     */
    private $feeByZipService;

    /**
     * @var CustomerFeeService
     */
    private $customerFeeService;

    /**
     * @param CustomerFeeByStateService $customerFeeByStateService
     * @param CustomerFeeByCountyService $feeByCountyService
     * @param CustomerFeeByZipService $feeByZipService
     * @param CustomerFeeService $customerFeeService
     */
    public function initialize(
        CustomerFeeByStateService $customerFeeByStateService,
        CustomerFeeByCountyService $feeByCountyService,
        CustomerFeeByZipService $feeByZipService,
        CustomerFeeService $customerFeeService
    )
    {
        $this->customerFeeByStateService = $customerFeeByStateService;
        $this->feeByCountyService = $feeByCountyService;
        $this->feeByZipService = $feeByZipService;
        $this->customerFeeService = $customerFeeService;
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @return Response
     */
    public function index($amcId, $customerId, $jobTypeId)
    {
        $fee = $this->customerFeeService->getByJobTypeId($amcId, $customerId, $jobTypeId);

        return $this->resource->makeAll(
            $this->customerFeeByStateService->getAll($fee->getId()),
            $this->transformer()
        );
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @param FeesByStateBulkProcessor $processor
     * @return Response
     */
    public function sync($amcId, $customerId, $jobTypeId, FeesByStateBulkProcessor $processor)
    {
        $fee = $this->customerFeeService->getByJobTypeId($amcId, $customerId, $jobTypeId);

        $result = $this->customerFeeByStateService->sync($fee->getId(), $processor->createPersistables());

        foreach ($processor->getApplyStateAmountToAllCounties() as $state => $flag){
            if (!$flag) {
                continue ;
            }

            $feeByState = $this->customerFeeByStateService->getByStateCode($fee->getId(), $state);
            $this->feeByCountyService->applyStateAmountToAllInState($feeByState->getId());
        }

        foreach ($processor->getApplyStateAmountToAllZips() as $state => $flag){
            if (!$flag) {
                continue ;
            }

            $feeByState = $this->customerFeeByStateService->getByStateCode($fee->getId(), $state);
            $this->feeByZipService->applyStateAmountToAllInState($feeByState->getId());
        }

        return $this->resource->makeAll($result, $this->transformer());
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @param string $state
     * @param FeesByStateProcessor $processor
     * @return Response
     */
    public function update($amcId, $customerId, $jobTypeId, $state, FeesByStateProcessor $processor)
    {
        $fee = $this->customerFeeService->getByJobTypeId($amcId, $customerId, $jobTypeId);

        $feeByState = $this->customerFeeByStateService->getByStateCode($fee->getId(), $state);

        $this->customerFeeByStateService->update(
            $feeByState->getId(),
            $processor->createPersistable(),
            $processor->schedulePropertiesToClear()
        );

        if ($processor->getApplyStateAmountToAllCounties()) {
            $this->feeByCountyService->applyStateAmountToAllInState($feeByState->getId());
        }

        if ($processor->getApplyStateAmountToAllZips()) {
            $this->feeByZipService->applyStateAmountToAllInState($feeByState->getId());
        }

        return $this->resource->blank();
    }

    /**
     * @param AmcService $amcService
     * @param CustomerFeeService $customerFeeService
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @return bool
     */
    public static function verifyAction(
        AmcService $amcService,
        CustomerFeeService $customerFeeService,
        $amcId,
        $customerId,
        $jobTypeId,
        $state = null
    )
    {
        if (!$amcService->exists($amcId)) {
            return false;
        }

        if (!$amcService->isRelatedWithCustomer($amcId, $customerId)) {
            return false;
        }

        if (!$amcService->hasCustomerFeeWithJobType($amcId, $customerId, $jobTypeId)) {
            return false;
        }

        if ($state) {
            $fee = $customerFeeService->getByJobTypeId($amcId, $customerId, $jobTypeId);

            if (!$customerFeeService->hasCustomerFeeByStateByStateCode($fee->getId(), $state)) {
                return false;
            }
        }

        return true;
    }
}
