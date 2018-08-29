<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Ascope\Libraries\Validation\PresentableException;
use Illuminate\Http\Response;
use ValuePad\Api\Assignee\V2_0\Processors\FeeProcessor;
use ValuePad\Api\Appraiser\V2_0\Processors\UpdateFeesBulkProcessor;
use ValuePad\Api\Assignee\V2_0\Transformers\CustomerFeeTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\SelectableProcessor;
use ValuePad\Core\Appraiser\Exceptions\JobTypeAlreadyTakenException;
use ValuePad\Core\Appraiser\Exceptions\UnableAccessJobTypeException;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Appraiser\Services\CustomerFeeService;
use ValuePad\Core\Shared\Exceptions\InvalidAmountException;
use ValuePad\Core\Shared\Exceptions\UniqueViolationException;

class CustomerFeesController extends BaseController
{
	/**
	 * @var CustomerFeeService
	 */
	private $feeService;

	/**
	 * @param CustomerFeeService $feeService
	 */
	public function initialize(CustomerFeeService $feeService)
	{
		$this->feeService = $feeService;
	}

	/**
	 * @param int $appraiserId
	 * @param int $customerId
	 * @return Response
	 */
	public function index($appraiserId, $customerId)
	{
		return $this->resource->makeAll(
			$this->feeService->getAll($appraiserId, $customerId),
			$this->transformer(CustomerFeeTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @param int $customerId
	 * @param FeeProcessor $processor
	 * @return Response
	 */
	public function store($appraiserId, $customerId, FeeProcessor $processor)
	{
		if (!$processor->isBulk()){
			return $this->resource->make(
				$this->feeService->create($appraiserId, $customerId, $processor->createPersistable()),
				$this->transformer(CustomerFeeTransformer::class)
			);
		}

		try {
			$fees = $this->feeService->createBulk($appraiserId, $customerId, $processor->createPersistables());
		} catch (PresentableException $ex){
			ErrorsThrowableCollection::throwError('bulk', new Error('invalid', $ex->getMessage()));
		}

		return $this->resource->makeAll($fees, $this->transformer(CustomerFeeTransformer::class));
	}

	/**
	 * @param int $appraiserId
	 * @param int $customerId
	 * @param int $feeId
	 * @param FeeProcessor $processor
	 * @return Response
	 */
	public function update($appraiserId, $customerId, $feeId, FeeProcessor $processor)
	{
		$this->feeService->update(
			$feeId,
			$processor->createPersistable(),
			$processor->schedulePropertiesToClear()
		);

		return $this->resource->blank();
	}

	/**
	 * @param int $appraiserId
	 * @param int $customerId
	 * @param UpdateFeesBulkProcessor $processor
	 * @return Response
	 */
	public function updateBulk($appraiserId, $customerId, UpdateFeesBulkProcessor $processor)
	{
		try {
			$this->feeService->updateBulkSharedAmongAssigneeAndCustomer(
				$appraiserId, $customerId, $processor->getAmounts());
		} catch (PresentableException $ex){
			ErrorsThrowableCollection::throwError('bulk', new Error('invalid', $ex->getMessage()));
		}

		return $this->resource->blank();
	}

	/**
	 * @param $appraiserId
	 * @param $customerId
	 * @param SelectableProcessor $processor
	 * @return Response
	 */
	public function destroyBulk($appraiserId, $customerId, SelectableProcessor $processor)
	{
		$this->feeService->deleteBulkSharedAmongAssigneeAndCustomer($appraiserId, $customerId, $processor->getIds());

		return $this->resource->blank();
	}

	/**
	 * @param int $appraiser
	 * @param int $customerId
	 * @param int $feeId
	 * @return Response
	 */
	public function destroy($appraiser, $customerId, $feeId)
	{
		$this->feeService->delete($feeId);

		return $this->resource->blank();
	}

	/**
	 * @param AppraiserService $appraiserService
	 * @param int $appraiserId
	 * @param int $customerId
	 * @param int $feeId
	 * @return bool
	 */
	public static function verifyAction(
		AppraiserService $appraiserService,
		$appraiserId,
		$customerId,
		$feeId = null
	)
	{
		if (!$appraiserService->exists($appraiserId)){
			return false;
		}

		if (!$appraiserService->hasPendingInvitationFromCustomer($appraiserId, $customerId)
				&& !$appraiserService->isRelatedWithCustomer($appraiserId, $customerId)){
			return false;
		}

		if ($feeId === null){
			return true;
		}

		return $appraiserService->hasCustomerFee($appraiserId, $customerId, $feeId);
	}
}
