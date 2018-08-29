<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\RevisionsProcessor;
use ValuePad\Api\Appraisal\V2_0\Transformers\RevisionTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Services\RevisionService;
use ValuePad\Core\Customer\Services\CustomerService;

class RevisionsController extends BaseController
{
	/**
	 * @var RevisionService
	 */
	private $revisionService;

	/**
	 * @param RevisionService $revisionService
	 */
	public function initialize(RevisionService $revisionService)
	{
		$this->revisionService = $revisionService;
	}

	/**
	 * @param int $customerId
	 * @param int $orderId
	 * @param RevisionsProcessor $processor
	 * @return Response
	 */
	public function store($customerId, $orderId, RevisionsProcessor $processor)
	{
		return $this->resource->make(
			$this->revisionService->create($orderId, $processor->createPersistable()),
			$this->transformer(RevisionTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $orderId
	 * @return Response
	 */
	public function index($customerId, $orderId)
	{
		return $this->resource->makeAll(
			$this->revisionService->getAll($orderId),
			$this->transformer(RevisionTransformer::class)
		);
	}

	/**
	 * @param CustomerService $customerService
	 * @param int $customerId
	 * @param int $orderId
	 * @return bool
	 */
	public static function verifyAction(CustomerService $customerService, $customerId, $orderId)
	{
		if (!$customerService->exists($customerId)){
			return false;
		}

		return $customerService->hasOrder($customerId, $orderId);
	}
}
