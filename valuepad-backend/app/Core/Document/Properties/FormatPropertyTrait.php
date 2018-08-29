<?php
namespace ValuePad\Core\Document\Properties;

use ValuePad\Core\Document\Enums\Format;

trait FormatPropertyTrait
{
	/**
	 * @var Format
	 */
	private $format;

	/**
	 * @return Format
	 */
	public function getFormat()
	{
		return $this->format;
	}

	/**
	 * @param Format $format
	 */
	public function setFormat(Format $format)
	{
		$this->format = $format;
	}
}
