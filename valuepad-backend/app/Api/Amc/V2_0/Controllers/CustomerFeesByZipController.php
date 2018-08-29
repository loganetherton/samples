<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Amc\V2_0\Processors\FeesByZipProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Services\CustomerFeeByZipService;
use ValuePad\Core\Amc\Services\CustomerFeeService;
use ValuePad\Core\Location\Services\StateService;

class CustomerFeesByZipController extends BaseController
{
    /**
     * @var CustomerFeeByZipService
     */
    private $feeByZipService;

    /**
     * @var CustomerFeeService
     */
    private $customerFeeService;

    /**
     * @param CustomerFeeByZipService $feeByZipService
     * @param CustomerFeeService $customerFeeService
     */
    public function initialize(CustomerFeeByZipService $feeByZipService, CustomerFeeService $customerFeeService)
    {
        $this->feeByZipService = $feeByZipService;
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
            $this->feeByZipService->getAllByStateCode($fee->getId(), $state),
            $this->transformer()
        );
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @param string $state
     * @param FeesByZipProcessor $processor
     * @return Response
     */
    public function sync($amcId, $customerId, $jobTypeId, $state, FeesByZipProcessor $processor)
    {
        $fee = $this->customerFeeService->getByJobTypeId($amcId, $customerId, $jobTypeId);

        return $this->resource->makeAll(
            $this->feeByZipService->syncInState($fee->getId(), $state, $processor->createPersistables()),
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
