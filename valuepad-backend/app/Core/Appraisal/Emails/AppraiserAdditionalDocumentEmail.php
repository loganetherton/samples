<?php
namespace ValuePad\Core\Appraisal\Emails;

use ValuePad\Core\Appraisal\Entities\AdditionalDocument;
use ValuePad\Core\Support\Letter\Email;

class AppraiserAdditionalDocumentEmail extends Email
{
	/**
	 * @var AdditionalDocument
	 */
	private $additionalDocument;

	/**
	 * @param AdditionalDocument $additionalDocument
	 */
	public function __construct(AdditionalDocument $additionalDocument)
	{
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
