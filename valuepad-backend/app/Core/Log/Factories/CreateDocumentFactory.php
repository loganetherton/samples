<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Log\Enums\Action;

class CreateDocumentFactory extends AbstractDocumentFactory
{
	/**
	 * @return Action
	 */
	public function getAction()
	{
		return new Action(Action::CREATE_DOCUMENT);
	}

	/**
	 * @param CreateDocumentNotification $notification
	 * @return Document
	 */
	protected function getDocument($notification)
	{
		return $notification->getDocument()->getPrimary();
	}
}
