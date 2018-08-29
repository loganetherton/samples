<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use ValuePad\Api\Customer\V2_0\Processors\AdditionalDocumentTypesProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\AdditionalDocumentTypeTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Customer\Services\AdditionalDocumentTypeService;
use ValuePad\Core\Customer\Services\CustomerService;
use Illuminate\Http\Response;

class AdditionalDocumentTypesController extends BaseController
{
	/**
	 * @var AdditionalDocumentTypeService
	 */
	private $additionalDocumentTypeService;

	/**
	 * @param AdditionalDocumentTypeService $additionalDocumentTypeService
	 */
	public function initialize(AdditionalDocumentTypeService $additionalDocumentTypeService)
	{
		$this->additionalDocumentTypeService = $additionalDocumentTypeService;
	}

	/**
	 * @param int $customerId
	 * @return Response
	 */
	public function index($customerId)
	{
		return $this->resource->makeAll(
			$this->additionalDocumentTypeService->getAll($customerId),
			$this->transformer(AdditionalDocumentTypeTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param AdditionalDocumentTypesProcessor $processor
	 * @return Response
	 */
	public function store($customerId, AdditionalDocumentTypesProcessor $processor)
	{
		return $this->resource->make(
			$this->additionalDocumentTypeService->create($customerId, $processor->getTitle()),
			$this->transformer(AdditionalDocumentTypeTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $typeId
	 * @param AdditionalDocumentTypesProcessor $processor
	 * @return Response
	 */
	public function update($customerId, $typeId, AdditionalDocumentTypesProcessor $processor)
	{
		$this->additionalDocumentTypeService->update($typeId, $processor->getTitle());

		return $this->resource->blank();
	}

	/**
	 * @param int $customerId
	 * @param int $typeId
	 * @return Response
	 */
	public function destroy($customerId, $typeId)
	{
		$this->additionalDocumentTypeService->delete($typeId);

		return $this->resource->blank();
	}

	/**
	 * @param CustomerService $customerService
	 * @param int $customerId
	 * @param int $typeId
	 * @return bool
	 */
	public static function verifyAction(CustomerService $customerService, $customerId, $typeId = null)
	{
		if (!$customerService->exists($customerId)){
			return false;
		}

		if ($typeId === null){
			return true;
		}

		return $customerService->hasAdditionalDocumentType($customerId, $typeId);
	}
}
