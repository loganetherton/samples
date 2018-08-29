<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Ascope\Libraries\Validation\PresentableException;
use Ascope\Libraries\Verifier\Action;
use Illuminate\Http\Response;
use ValuePad\Api\Appraiser\V2_0\Processors\UpdateFeesBulkProcessor;
use ValuePad\Api\Assignee\V2_0\Processors\FeeProcessor;
use ValuePad\Api\Assignee\V2_0\Transformers\CustomerFeeTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\SelectableProcessor;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Services\CustomerFeeService;
use ValuePad\Core\Customer\Services\CustomerService;

class CustomerFeesController extends BaseController
{
    /**
     * @var CustomerFeeService
     */
    private $feeService;

    /**
     * @param CustomerFeeService $feeService;
     */
    public function initialize(CustomerFeeService $feeService)
    {
        $this->feeService = $feeService;
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @return Response
     */
    public function index($amcId, $customerId)
    {
        return $this->resource->makeAll(
            $this->feeService->getAll($amcId, $customerId),
            $this->transformer(CustomerFeeTransformer::class)
        );
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param FeeProcessor $processor
     * @return Response
     */
    public function store($amcId, $customerId, FeeProcessor $processor)
    {
        if (!$processor->isBulk()){
            return $this->resource->make(
                $this->feeService->create($amcId, $customerId, $processor->createPersistable()),
                $this->transformer(CustomerFeeTransformer::class)
            );
        }

        try {
            $fees = $this->feeService->createBulk($amcId, $customerId, $processor->createPersistables());
        } catch (PresentableException $ex){
            ErrorsThrowableCollection::throwError('bulk', new Error('invalid', $ex->getMessage()));
        }

        return $this->resource->makeAll($fees, $this->transformer(CustomerFeeTransformer::class));
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $feeId
     * @param FeeProcessor $processor
     * @return Response
     */
    public function update($amcId, $customerId, $feeId, FeeProcessor $processor)
    {
        $this->feeService->update(
            $feeId,
            $processor->createPersistable(),
            $processor->schedulePropertiesToClear()
        );

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param UpdateFeesBulkProcessor $processor
     * @return Response
     */
    public function updateBulk($amcId, $customerId, UpdateFeesBulkProcessor $processor)
    {
        try {
            $this->feeService->updateBulkSharedAmongAssigneeAndCustomer(
                $amcId, $customerId, $processor->getAmounts());
        } catch (PresentableException $ex){
            ErrorsThrowableCollection::throwError('bulk', new Error('invalid', $ex->getMessage()));
        }

        return $this->resource->blank();
    }

    /**
     * @param $amcId
     * @param $customerId
     * @param SelectableProcessor $processor
     * @return Response
     */
    public function destroyBulk($amcId, $customerId, SelectableProcessor $processor)
    {
        $this->feeService->deleteBulkSharedAmongAssigneeAndCustomer($amcId, $customerId, $processor->getIds());

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $feeId
     * @return Response
     */
    public function destroy($amcId, $customerId, $feeId)
    {
        $this->feeService->delete($feeId);

        return $this->resource->blank();
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @param int $jobTypeId
     * @param int $zip
     * @return Response
     */
    public function showFeeByZip($amcId, $customerId, $jobTypeId, $zip)
    {
        $amount = $this->feeService->determineAmountByJobTypeIdAndZip($amcId, $customerId, $jobTypeId, $zip);

        return $this->resource->make(['amount' => $amount]);
    }

    /**
     * @param int $amcId
     * @param int $customerId
     * @return Response
     */
    public function applyDefaultLocationFees($amcId, $customerId)
    {
        $this->feeService->syncWithDefaultLocationFees($amcId, $customerId);

        return $this->resource->blank();
    }

    /**
     * @param Action $action
     * @param AmcService $amcService
     * @param CustomerService $customerService
     * @param int $amcId
     * @param int $customerId
     * @param int $feeIdOrJobTypeId
     * @return bool
     */
    public static function verifyAction(
        Action $action,
        AmcService $amcService,
        CustomerService $customerService,
        $amcId,
        $customerId,
        $feeIdOrJobTypeId = null
    )
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        if (!$amcService->isRelatedWithCustomer($amcId, $customerId)){
            return false;
        }

        if ($feeIdOrJobTypeId === null){
            return true;
        }

        if ($action->is('showFeeByZip')){
            return $customerService->hasPayableJobType($customerId, $feeIdOrJobTypeId);
        }

        return $amcService->hasCustomerFee($amcId, $customerId, $feeIdOrJobTypeId);
    }
}
