<?php
namespace ValuePad\Core\Appraisal\Notifications;

use ValuePad\Core\Appraisal\Entities\Order;

abstract class AbstractNotification
{
	/**
	 * @var Order
	 */
	private $order;

	/**
	 * @param Order $order
	 */
	public function __construct(Order $order)
	{
		$this->order = $order;
	}

	/**
	 * @return Order
	 */
	public function getOrder()
	{
		return $this->order;
	}
}
