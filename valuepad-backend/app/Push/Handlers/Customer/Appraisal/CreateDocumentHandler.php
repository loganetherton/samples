<?php
namespace ValuePad\Push\Handlers\Customer\Appraisal;

use ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification;

class CreateDocumentHandler extends BaseHandler
{
	/**
	 * @param CreateDocumentNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		return [
			'type' => 'order',
			'event' => 'create-document',
			'order' => $notification->getOrder()->getId(),
			'document' => $notification->getDocument()->getId()
		];
	}
}
