<?php
namespace ValuePad\Core\Appraisal\Options;

/**
 * The option tells whether a validation should consider the supported formats provided by a customer
 *
 *
 */
trait CheckDocumentSupportedFormatsOptionTrait
{
	/**
	 * @var bool
	 */
	private $checkDocumentSupportedFormats = true;

	/**
	 * @param bool $flag
	 */
	public function setCheckDocumentSupportedFormats($flag)
	{
		$this->checkDocumentSupportedFormats = $flag;
	}

	/**
	 * @return bool
	 */
	public function getCheckDocumentSupportedFormats()
	{
		return $this->checkDocumentSupportedFormats;
	}
}
