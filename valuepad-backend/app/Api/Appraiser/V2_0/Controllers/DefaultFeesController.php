<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Ascope\Libraries\Validation\PresentableException;
use Illuminate\Http\Response;
use ValuePad\Api\Assignee\V2_0\Processors\FeeProcessor;
use ValuePad\Api\Appraiser\V2_0\Processors\UpdateFeesBulkProcessor;
use ValuePad\Api\Appraiser\V2_0\Transformers\DefaultFeeTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\SelectableProcessor;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Appraiser\Services\DefaultFeeService;

class DefaultFeesController extends BaseController
{
	/**
	 * @var DefaultFeeService
	 */
	private $feeService;

	/**
	 * @param DefaultFeeService $feeService
	 */
	public function initialize(DefaultFeeService $feeService)
	{
		$this->feeService = $feeService;
	}

	/**
	 * @param int $appraiserId
	 * @return Response
	 */
	public function index($appraiserId)
	{
		return $this->resource->makeAll(
			$this->feeService->getAll($appraiserId),
			$this->transformer(DefaultFeeTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @param FeeProcessor $processor
	 * @return Response
	 */
	public function store($appraiserId, FeeProcessor $processor)
	{
		if (!$processor->isBulk()){
			return $this->resource->make(
				$this->feeService->create($appraiserId, $processor->createPersistable()),
				$this->transformer(DefaultFeeTransformer::class)
			);
		}

		try {
			$fees = $this->feeService->createBulk($appraiserId, $processor->createPersistables());
		} catch (PresentableException $ex)
		{
			ErrorsThrowableCollection::throwError('bulk', new Error('invalid', $ex->getMessage()));
		}

		return $this->resource->makeAll($fees, $this->transformer(DefaultFeeTransformer::class));
	}

	/**
	 * @param int $appraiserId
	 * @param int $feeId
	 * @param FeeProcessor $processor
	 * @return Response
	 */
	public function update($appraiserId, $feeId, FeeProcessor $processor)
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
	 * @param UpdateFeesBulkProcessor $processor
	 * @return Response
	 */
	public function updateBulk($appraiserId, UpdateFeesBulkProcessor $processor)
	{
		try {
			$this->feeService->updateBulkOwningByAppraiser($appraiserId, $processor->getAmounts());
		} catch (PresentableException $ex){
			ErrorsThrowableCollection::throwError('bulk', new Error('invalid', $ex->getMessage()));
		}

		return $this->resource->blank();
	}

	/**
	 * @param int $appraiserId
	 * @param SelectableProcessor $processor
	 * @return Response
	 */
	public function destroyBulk($appraiserId, SelectableProcessor $processor)
	{
		$this->feeService->deleteBulkOwningByAppraiser($appraiserId, $processor->getIds());

		return $this->resource->blank();
	}


	/**
	 * @param int $appraiser
	 * @param int $feeId
	 * @return Response
	 */
	public function destroy($appraiser, $feeId)
	{
		$this->feeService->delete($feeId);

		return $this->resource->blank();
	}

	/**
	 * @param AppraiserService $appraiserService
	 * @param int $appraiserId
	 * @param int $feeId
	 * @return bool
	 */
	public static function verifyAction(
		AppraiserService $appraiserService,
		$appraiserId,
		$feeId = null
	)
	{
		if (!$appraiserService->exists($appraiserId)){
			return false;
		}

		if ($feeId === null){
			return true;
		}

		return $appraiserService->hasDefaultFee($appraiserId, $feeId);
	}
}
