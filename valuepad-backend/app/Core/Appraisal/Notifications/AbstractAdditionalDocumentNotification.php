<?php
namespace ValuePad\Core\Appraisal\Notifications;

use ValuePad\Core\Appraisal\Entities\AdditionalDocument;

abstract class AbstractAdditionalDocumentNotification extends AbstractNotification
{
	/**
	 * @var AdditionalDocument
	 */
	private $additionalDocument;

	public function __construct(AdditionalDocument $additionalDocument)
	{
		parent::__construct($additionalDocument->getOrder());
		$this->additionalDocument = $additionalDocument;
	}

	/**
	 * @return AdditionalDocument
	 */
	public function getAdditionalDocument()
	{
		return $this->additionalDocument;
	}
}
