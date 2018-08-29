<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Appraisal\Notifications\AbstractAdditionalDocumentNotification;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Log\Entities\Log;
use ValuePad\Core\Log\Extras\Extra;

abstract class AbstractAdditionalDocumentFactory extends AbstractDocumentFactory
{
	/**
	 * @param AbstractAdditionalDocumentNotification $notification
	 * @return Log
	 */
	public function create($notification)
	{
		$log = parent::create($notification);

		$type = $notification->getAdditionalDocument()->getType();

		if ($type){
			$type = $type->getTitle();
		} else {
			$type = $notification->getAdditionalDocument()->getLabel();
		}

		$log->getExtra()[Extra::TYPE] = $type;

		return $log;
	}

	/**
	 * @param AbstractAdditionalDocumentNotification $notification
	 * @return Document
	 */
	protected function getDocument($notification)
	{
		return $notification->getAdditionalDocument()->getDocument();
	}
}
