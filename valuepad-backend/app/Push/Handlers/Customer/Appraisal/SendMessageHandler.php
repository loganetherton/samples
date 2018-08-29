<?php
namespace ValuePad\Push\Handlers\Customer\Appraisal;

use ValuePad\Core\Appraisal\Notifications\SendMessageNotification;

class SendMessageHandler extends BaseHandler
{
	/**
	 * @param SendMessageNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		return [
			'type' => 'order',
			'event' => 'send-message',
			'order' => $notification->getOrder()->getId(),
			'message' => $notification->getMessage()->getId()
		];
	}
}
