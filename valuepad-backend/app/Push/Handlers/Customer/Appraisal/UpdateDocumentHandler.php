<?php
namespace ValuePad\Push\Handlers\Customer\Appraisal;

use ValuePad\Core\Appraisal\Notifications\UpdateDocumentNotification;

class UpdateDocumentHandler extends BaseHandler
{
	/**
	 * @param UpdateDocumentNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		return [
			'type' => 'order',
			'event' => 'update-document',
			'order' => $notification->getOrder()->getId(),
			'document' => $notification->getDocument()->getId()
		];
	}
}
