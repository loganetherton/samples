<?php
namespace ValuePad\Core\Appraisal\Emails;

use ValuePad\Core\Appraisal\Entities\Document;
use ValuePad\Core\Support\Letter\Email;

class AppraiserDocumentEmail extends Email
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
