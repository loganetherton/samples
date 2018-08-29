<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use Ascope\Libraries\Validation\PresentableException;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Processors\BidsProcessor;
use ValuePad\Api\Appraisal\V2_0\Transformers\BidTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Options\CreateBidOptions;
use ValuePad\Core\Appraisal\Options\UpdateBidOptions;
use ValuePad\Core\Appraisal\Services\BidService;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Customer\Services\CustomerService;

class BidController extends BaseController
{
	/**
	 * @var BidService
	 */
	private $bidService;

	/**
	 * @var OrderService
	 */
	private $orderService;

	/**
	 * @var CustomerService $customerService
	 */
	private $customerService;

	/**
	 * @param BidService $bidService
	 * @param OrderService $orderService
	 * @param CustomerService $customerService
	 */
	public function initialize(BidService $bidService, OrderService $orderService, CustomerService $customerService)
	{
		$this->bidService = $bidService;
		$this->orderService = $orderService;
		$this->customerService = $customerService;
	}

	/**
	 * @param int $customerId
	 * @param int $orderId
	 * @param BidsProcessor $processor
	 * @return Response
	 */
	public function store($customerId, $orderId, BidsProcessor $processor)
	{
		$options = new CreateBidOptions();
		$options->requireEstimatedCompletionDate(false);

		$appraiser = $this->orderService->get($orderId)->getAssignee();

		if (!$this->customerService->isRelatedWithAppraiser($customerId, $appraiser->getId())){
			throw new PresentableException(
				'Unable to submit a bid on behalf of an appraiser that\'s not connected to the provided customer.');
		}

		return $this->resource->make(
			$this->bidService->create($orderId, $processor->createPersistable(), $options),
			$this->transformer(BidTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $orderId
	 * @return Response
	 */
	public function show($customerId, $orderId)
	{
		if (!$this->orderService->hasBid($orderId)){
			return $this->resource->error()->notFound();
		}

		return $this->resource->make(
			$this->bidService->get($orderId),
			$this->transformer(BidTransformer::class)
		);
	}

	/**
	 * @param int $customerId
	 * @param int $orderId
	 * @param BidsProcessor $processor
	 * @return Response
	 */
	public function update($customerId, $orderId, BidsProcessor $processor)
	{
		if (!$this->orderService->hasBid($orderId)){
			return $this->resource->error()->notFound();
		}

		$options = new UpdateBidOptions();
		$options->requireEstimatedCompletionDate(false);

		$this->bidService->update(
			$orderId,
			$processor->createPersistable(),
			$processor->schedulePropertiesToClear($options)
		);

		return $this->resource->blank();
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
