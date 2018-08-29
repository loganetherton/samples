<?php
namespace ValuePad\Live\Handlers;

use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Core\Appraisal\Notifications\AbstractNotification;

abstract class AbstractDataAwareOrderHandler extends AbstractOrderHandler
{
	/**
	 * @param AbstractNotification $notification
	 * @return array
	 */
	protected function getData($notification)
	{
		return $this->transformer(OrderTransformer::class)
			->setIncludes(['processStatus'])
			->transform($notification->getOrder());
	}
}
