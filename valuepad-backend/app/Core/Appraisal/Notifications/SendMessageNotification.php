<?php
namespace ValuePad\Core\Appraisal\Notifications;

use ValuePad\Core\Appraisal\Entities\Message;
use ValuePad\Core\Appraisal\Entities\Order;

class SendMessageNotification extends AbstractNotification
{
	/**
	 * @var Message
	 */
	private $message;

	public function __construct(Order $order, Message $message)
	{
		parent::__construct($order);

		$this->message = $message;
	}

	/**
	 * @return Message
	 */
	public function getMessage()
	{
		return $this->message;
	}
}
