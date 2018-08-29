<?php
namespace ValuePad\Push\Handlers\Customer\Appraisal;

use ValuePad\Core\Appraisal\Notifications\PayTechFeeNotification;

class PayTechFeeHandler extends BaseHandler
{
	/**
	 * @param PayTechFeeNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		return [
			'type' => 'order',
			'event' => 'pay-tech-fee',
			'order' => $notification->getOrder()->getId()
		];
	}
}
