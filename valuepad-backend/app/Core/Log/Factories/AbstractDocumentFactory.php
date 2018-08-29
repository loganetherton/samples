<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\AbstractNotification;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Interfaces\DocumentPreferenceInterface;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Enums\Action;
use ValuePad\Core\Log\Extras\Extra;
use ValuePad\Support\Shortcut;

abstract class AbstractDocumentFactory extends AbstractFactory
{
	/**
	 * @param AbstractNotification $notification
	 * @return Log
	 */
	public function create($notification)
	{
		/**
		 * @var DocumentPreferenceInterface $preference
		 */
		$preference = $this->container->get(DocumentPreferenceInterface::class);

		$log = parent::create($notification);

		$log->setAction($this->getAction());

		$document = $this->getDocument($notification);

		$extra = $log->getExtra();

		$extra[Extra::NAME] = $document->getName();
		$extra[Extra::FORMAT] = (string) $document->getFormat();
		$extra[Extra::URL] = Shortcut::extractUrlFromDocument($document, $preference);
		$extra[Extra::SIZE] = $document->getSize();

		return $log;
	}

	/**
	 * @param object $notification
	 * @return Document
	 */
	abstract protected function getDocument($notification);

	/**
	 * @return Action
	 */
	abstract protected function getAction();
}
