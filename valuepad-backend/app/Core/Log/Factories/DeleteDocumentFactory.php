<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\DeleteDocumentNotification;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Log\Enums\Action;

class DeleteDocumentFactory extends AbstractDocumentFactory
{
	/**
	 * @return Action
	 */
	public function getAction()
	{
		return new Action(Action::DELETE_DOCUMENT);
	}

	/**
	 * @param DeleteDocumentNotification $notification
	 * @return Document
	 */
	protected function getDocument($notification)
	{
		return $notification->getDocument()->getPrimary();
	}
}
