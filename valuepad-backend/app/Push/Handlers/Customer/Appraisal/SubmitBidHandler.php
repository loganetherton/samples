<?php
namespace ValuePad\Push\Handlers\Customer\Appraisal;

use ValuePad\Core\Appraisal\Notifications\SubmitBidNotification;

class SubmitBidHandler extends BaseHandler
{
	/**
	 * @param SubmitBidNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		return [
			'type' => 'order',
			'event' => 'submit-bid',
			'order' => $notification->getOrder()->getId()
		];
	}
}
