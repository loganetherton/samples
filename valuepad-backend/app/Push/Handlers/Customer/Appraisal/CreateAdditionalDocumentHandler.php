<?php
namespace ValuePad\Push\Handlers\Customer\Appraisal;

use ValuePad\Core\Appraisal\Notifications\CreateAdditionalDocumentNotification;

class CreateAdditionalDocumentHandler extends BaseHandler
{
	/**
	 * @param CreateAdditionalDocumentNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		return [
			'type' => 'order',
			'event' => 'create-additional-document',
			'order' => $notification->getOrder()->getId(),
			'additionalDocument' => $notification->getAdditionalDocument()->getId()
		];
	}
}
