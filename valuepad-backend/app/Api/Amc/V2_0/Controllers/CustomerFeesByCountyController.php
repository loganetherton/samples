<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Amc\V2_0\Processors\FeesByCountyProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Services\CustomerFeeByCountyService;
use ValuePad\Core\Amc\Services\CustomerFeeService;
use ValuePad\Core\Location\Services\StateService;

class CustomerFeesByCountyController extends BaseController
{
    /**
     * @var CustomerFeeByCountyService
     */
    private $feeByCountyService;

    /**
     * @var CustomerFeeService
     */
    private $customerFeeService;

    /**
     * @param CustomerFeeByCountyService $feeByCountyService
     * @param CustomerFeeService $customerFeeService
     */
    public function initialize(CustomerFeeByCountyService $feeByCountyService, CustomerFeeService $customerFeeService)
    {
        $this->feeByCountyService = $feeByCountyService;
        $this->customerFeeService = $customerFeeService;
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @param string $state
     * @return Response
     */
    public function index($amcId, $customerId, $jobTypeId, $state)
    {
        $fee = $this->customerFeeService->getByJobTypeId($amcId, $customerId, $jobTypeId);

        return $this->resource->makeAll(
            $this->feeByCountyService->getAllByStateCode($fee->getId(), $state),
            $this->transformer()
        );
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @param string $state
     * @param FeesByCountyProcessor $processor
     * @return Response
     */
    public function sync($amcId, $customerId, $jobTypeId, $state, FeesByCountyProcessor $processor)
    {
        $fee = $this->customerFeeService->getByJobTypeId($amcId, $customerId, $jobTypeId);

        return $this->resource->makeAll(
            $this->feeByCountyService->syncInState($fee->getId(), $state, $processor->createPersistables()),
            $this->transformer()
        );
    }

    /**
     * @param AmcService $amcService
     * @param StateService $stateService
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @param string $state
     * @return bool
     */
    public static function verifyAction(
        AmcService $amcService,
        StateService $stateService,
        $amcId,
        $customerId,
        $jobTypeId,
        $state
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

        return $stateService->exists($state);
    }
}
