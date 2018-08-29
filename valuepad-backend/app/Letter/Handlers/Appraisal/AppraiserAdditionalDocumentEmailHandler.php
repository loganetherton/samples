<?php
namespace ValuePad\Letter\Handlers\Appraisal;

use ValuePad\Core\Appraisal\Emails\AppraiserAdditionalDocumentEmail;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Document\Entities\Document;

class AppraiserAdditionalDocumentEmailHandler extends AbstractAppraiserDocumentEmailHandler
{
	/**
	 * @param AppraiserAdditionalDocumentEmail $source
	 * @return Order
	 */
	protected function getOrder($source)
	{
		return $source->getAdditionalDocument()->getOrder();
	}

	/**
	 * @param AppraiserAdditionalDocumentEmail $source
	 * @return Document
	 */
	protected function getDocument($source)
	{
		return $source->getAdditionalDocument()->getDocument();
	}
}
