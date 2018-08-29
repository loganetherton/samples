<?php
namespace ValuePad\Live\Handlers;

use ValuePad\Api\Appraisal\V2_0\Transformers\MessageTransformer;
use ValuePad\Core\Appraisal\Notifications\SendMessageNotification;

class SendMessageHandler extends AbstractOrderHandler
{
	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'send-message';
	}

	/**
	 * @param SendMessageNotification $notification
	 * @return array
	 */
	protected function getData($notification)
	{
		return $this->transformer(MessageTransformer::class)
			->transform($notification->getMessage());
	}
}
