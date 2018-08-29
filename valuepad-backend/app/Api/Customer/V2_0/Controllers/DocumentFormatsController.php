<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\DocumentFormatsProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\DocumentSupportedFormatsTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Customer\Services\DocumentSupportedFormatsService;

class DocumentFormatsController extends BaseController
{
	/**
	 * @var DocumentSupportedFormatsService
	 */
	private $formatsService;

	public function initialize(DocumentSupportedFormatsService $formatsService)
	{
		$this->formatsService = $formatsService;
	}

	/**
	 * @param int $customerId
	 * @return Response
	 */
	public function index($customerId)
	{
		return $this->resource->makeAll(
			$this->formatsService->getAll($customerId),
			$this->transformer(DocumentSupportedFormatsTransformer::class)
		);
	}

	/**
	 * @param $customerId
	 * @param DocumentFormatsProcessor $processor
	 * @return Response
	 */
	public function store($customerId, DocumentFormatsProcessor $processor)
	{
		return $this->resource->make(
			$this->formatsService->create($customerId, $processor->createPersistable()),
			$this->transformer(DocumentSupportedFormatsTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $formatId
	 * @param DocumentFormatsProcessor $processor
	 * @return Response
	 */
	public function update($customerId, $formatId, DocumentFormatsProcessor $processor)
	{
		$this->formatsService->update(
			$formatId,
			$processor->createPersistable(),
			$processor->schedulePropertiesToClear()
		);

		return $this->resource->blank();
	}

	/**
	 * @param int $customerId
	 * @param int $formatId
	 * @return Response
	 */
	public function destroy($customerId, $formatId)
	{
		$this->formatsService->delete($formatId);

		return $this->resource->blank();
	}

	/**
	 * @param CustomerService $customerService
	 * @param int $customerId
	 * @param int $formatId
	 * @return bool
	 */
	public static function verifyAction(
		CustomerService $customerService,
		$customerId,
		$formatId = null
	)
	{
		if (!$customerService->exists($customerId)){
			return false;
		}

		if ($formatId === null){
			return true;
		}

		return $customerService->hasDocumentSupportedFormats($customerId, $formatId);
	}
}
