<?php
namespace ValuePad\Push\Handlers\Customer\Appraisal;

use ValuePad\Core\Appraisal\Notifications\DeclineOrderNotification;

class DeclineOrderHandler extends BaseHandler
{
	/**
	 * @param DeclineOrderNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		return [
			'type' => 'order',
			'event' => 'decline',
			'order' => $notification->getOrder()->getId(),
			'reason' => $notification->getReason()->value(),
			'message' => $notification->getMessage()
		];
	}
}
