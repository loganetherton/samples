<?php
namespace ValuePad\Core\Appraisal\Notifications;

use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Objects\Conditions;


class AcceptOrderWithConditionsNotification extends AbstractNotification
{
	/**
	 * @var Conditions
	 */
	private $conditions;

	public function __construct(Order $order, Conditions $conditions)
	{
		parent::__construct($order);

		$this->conditions = $conditions;
	}

	/**
	 * @return Conditions
	 */
	public function getConditions()
	{
		return $this->conditions;
	}
}
