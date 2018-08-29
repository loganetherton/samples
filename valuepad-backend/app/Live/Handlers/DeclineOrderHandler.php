<?php
namespace ValuePad\Live\Handlers;

use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Core\Appraisal\Notifications\DeclineOrderNotification;

class DeclineOrderHandler extends AbstractOrderHandler
{
	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'decline';
	}

	/**
	 * @param DeclineOrderNotification $notification
	 * @return array
	 */
	protected function getData($notification)
	{
		$reason = $notification->getReason();

		if ($reason !== null){
			$reason = $reason->value();
		}

		return [
			'order' => $this->transformer(OrderTransformer::class)->transform($notification->getOrder()),
			'reason' =>  $reason,
			'message' => $notification->getMessage()
		];
	}
}
