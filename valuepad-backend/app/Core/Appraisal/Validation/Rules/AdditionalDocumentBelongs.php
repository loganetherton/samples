<?php
namespace ValuePad\Core\Appraisal\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Services\OrderService;

class AdditionalDocumentBelongs extends AbstractRule
{
	/**
	 * @var OrderService
	 */
	private $orderService;

	/**
	 * @var Order
	 */
	private $order;

	/**
	 * @param OrderService $orderService
	 * @param Order $order
	 */
	public function __construct(OrderService $orderService, Order $order)
	{
		$this->orderService = $orderService;
		$this->order = $order;

		$this->setIdentifier('not-belong');
		$this->setMessage('The provided additional document does not belong to the specified order.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if ($value == object_take($this->order, 'contractDocument.id')){
			return null;
		}

		if (!$this->orderService->hasAdditionalDocument($this->order->getId(), $value)){
			return $this->getError();
		}

		return null;
	}
}
