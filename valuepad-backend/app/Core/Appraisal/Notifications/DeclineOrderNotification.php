<?php
namespace ValuePad\Core\Appraisal\Notifications;

use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Enums\DeclineReason;

class DeclineOrderNotification extends AbstractNotification
{
	/**
	 * @var DeclineReason
	 */
	private $reason;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @param Order $order
	 * @param DeclineReason $reason
	 * @param string $message
	 */
	public function __construct(Order $order, DeclineReason $reason, $message = null)
	{
		parent::__construct($order);
		$this->reason = $reason;
		$this->message = $message;
	}

	/**
	 * @return DeclineReason
	 */
	public function getReason()
	{
		return $this->reason;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}
}
