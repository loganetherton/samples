<?php
namespace ValuePad\Live\Handlers;

use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Api\Customer\V2_0\Transformers\AdditionalStatusTransformer;
use ValuePad\Core\Appraisal\Notifications\ChangeAdditionalStatusNotification;

class ChangeAdditionalStatusHandler extends AbstractOrderHandler
{
	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'change-additional-status';
	}

	/**
	 * @param ChangeAdditionalStatusNotification $notification
	 * @return array
	 */
	protected function getData($notification)
	{
		return [
			'order' => $this->transformer(OrderTransformer::class)->transform($notification->getOrder()),
			'oldAdditionalStatus' => $this->transformer(AdditionalStatusTransformer::class)
				->transform($notification->getOldAdditionalStatus()),
			'oldAdditionalStatusComment' => $notification->getOldAdditionalStatusComment(),
			'newAdditionalStatus' => $this->transformer(AdditionalStatusTransformer::class)
				->transform($notification->getNewAdditionalStatus()),
			'newAdditionalStatusComment' => $notification->getNewAdditionalStatusComment(),
		];
	}
}
