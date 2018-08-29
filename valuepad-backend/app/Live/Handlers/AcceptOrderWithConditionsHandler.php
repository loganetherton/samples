<?php
namespace ValuePad\Live\Handlers;

use ValuePad\Api\Appraisal\V2_0\Transformers\ConditionsTransformer;
use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Core\Appraisal\Notifications\AcceptOrderWithConditionsNotification;

class AcceptOrderWithConditionsHandler extends AbstractOrderHandler
{
	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'accept-with-conditions';
	}

	/**
	 * @param AcceptOrderWithConditionsNotification $notification
	 * @return array
	 */
	protected function getData($notification)
	{
		return [
			'order' => $this->transformer(OrderTransformer::class)->transform($notification->getOrder()),
			'conditions' => $this->transformer(ConditionsTransformer::class)->transform($notification->getConditions())
		];
	}
}
