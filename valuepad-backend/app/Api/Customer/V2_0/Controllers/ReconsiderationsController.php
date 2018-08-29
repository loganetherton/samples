<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Transformers\ReconsiderationTransformer;
use ValuePad\Api\Customer\V2_0\Processors\ReconsiderationsProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Services\ReconsiderationService;
use ValuePad\Core\Customer\Services\CustomerService;

class ReconsiderationsController extends BaseController
{
	/**
	 * @var ReconsiderationService
	 */
	private $reconsiderationService;

	/**
	 * @param ReconsiderationService $reconsiderationService
	 */
	public function initialize(ReconsiderationService $reconsiderationService)
	{
		$this->reconsiderationService = $reconsiderationService;
	}

	/**
	 * @param int $customerId
	 * @param int $orderId
	 * @return Response
	 */
	public function index($customerId, $orderId)
	{
		return $this->resource->makeAll(
			$this->reconsiderationService->getAll($orderId),
			$this->transformer(ReconsiderationTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $orderId
	 * @param ReconsiderationsProcessor $processor
	 * @return Response
	 */
	public function store($customerId, $orderId, ReconsiderationsProcessor $processor)
	{
		return $this->resource->make(
			$this->reconsiderationService->create($orderId, $processor->createPersistable()),
			$this->transformer(ReconsiderationTransformer::class)
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
