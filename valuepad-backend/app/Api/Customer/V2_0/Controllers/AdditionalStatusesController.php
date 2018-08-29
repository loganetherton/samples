<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\AdditionalStatusesProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\AdditionalStatusTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Customer\Services\AdditionalStatusService;
use ValuePad\Core\Customer\Services\CustomerService;

class AdditionalStatusesController extends BaseController
{
	/**
	 * @var AdditionalStatusService
	 */
	private $additionalStatusService;

	/**
	 * @param AdditionalStatusService $additionalStatusService
	 */
	public function initialize(AdditionalStatusService $additionalStatusService)
	{
		$this->additionalStatusService = $additionalStatusService;
	}

	/**
	 * @param int $customerId
	 * @return Response
	 */
	public function index($customerId)
	{
		return $this->resource->makeAll(
			$this->additionalStatusService->getAllActive($customerId),
			$this->transformer(AdditionalStatusTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param AdditionalStatusesProcessor $processor
	 * @return Response
	 */
	public function store($customerId, AdditionalStatusesProcessor $processor)
	{
		return $this->resource->make(
			$this->additionalStatusService->create($customerId, $processor->createPersistable()),
			$this->transformer(AdditionalStatusTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $additionalStatusId
	 * @param AdditionalStatusesProcessor $processor
	 * @return Response
	 */
	public function update($customerId, $additionalStatusId, AdditionalStatusesProcessor $processor)
	{
		$this->additionalStatusService->update(
			$additionalStatusId,
			$processor->createPersistable(),
			$processor->schedulePropertiesToClear()
		);

		$this->resource->blank();
	}

	/**
	 * @param int $customerId
	 * @param int $additionalStatusId
	 * @return Response
	 */
	public function destroy($customerId, $additionalStatusId)
	{
		$this->additionalStatusService->delete($additionalStatusId);
		return $this->resource->blank();
	}

	/**
	 * @param CustomerService $customerService
	 * @param int $customerId
	 * @param int $additionalStatusId
	 * @return bool
	 */
	public static function verifyAction(CustomerService $customerService, $customerId, $additionalStatusId = null)
	{
		if (!$customerService->exists($customerId)){
			return false;
		}

		if ($additionalStatusId === null){
			return true;
		}

		return $customerService->hasActiveAdditionalStatus($customerId, $additionalStatusId);
	}
}
