<?php
namespace ValuePad\Core\Appraisal\Objects;

use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\BadgeType;

class Badge
{
	/**
	 * @var BadgeType
	 */
	private $type;
	public function getType() { return $this->type; }
	public function setType(BadgeType $type) { $this->type = $type; }

	/**
	 * @var int
	 */
	private $counter = 0;
	public function getCounter() { return $this->counter; }
	public function increaseCounter() { $this->counter ++; }


	/**
	 * @var array
	 */
	private $position;
	public function getPosition() { return $this->position; }
	public function setPosition($position) { $this->position = $position; }

	/**
	 * @var Order[]
	 */
	private $orders;
	public function getOrders() { return $this->orders; }
	public function addOrder(Order $order) { $this->orders[] = $order; }
}
