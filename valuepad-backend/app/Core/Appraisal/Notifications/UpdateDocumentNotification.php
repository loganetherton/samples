<?php
namespace ValuePad\Core\Appraisal\Notifications;

use ValuePad\Core\Appraisal\Entities\Document;

class UpdateDocumentNotification extends AbstractNotification
{
	private $document;

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
