<?php
namespace ValuePad\Core\Appraisal\Notifications;

use ValuePad\Core\Appraisal\Entities\Document;

abstract class AbstractDocumentNotification extends AbstractNotification
{
	/**
	 * @var Document
	 */
	private $document;

	/**
	 * @param Document $document
	 */
	public function __construct(Document $document)
	{
		parent::__construct($document->getOrder());
		$this->document = $document;
	}

	/**
	 * @return Document
	 */
	public function getDocument()
	{
		return $this->document;
	}
}
