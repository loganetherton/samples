<?php
namespace ValuePad\Letter\Handlers\Appraisal;

use ValuePad\Core\Appraisal\Emails\AppraiserDocumentEmail;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Letter\Support\Job;

class AppraiserDocumentEmailHandler extends AbstractAppraiserDocumentEmailHandler
{
	/**
	 * @param AppraiserDocumentEmail $source
	 * @return Order
	 */
	protected function getOrder($source)
	{
		return $source->getDocument()->getOrder();
	}

	/**
	 * @param AppraiserDocumentEmail $source
	 * @return Document
	 */
	protected function getDocument($source)
	{
		return $source->getDocument()->getPrimary();
	}
}
