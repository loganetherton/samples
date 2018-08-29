<?php
namespace ValuePad\Core\Appraisal\Objects;

use ValuePad\Core\Customer\Enums\Formats;
use ValuePad\Core\Customer\Enums\ExtraFormats;

class DocumentSupportedFormats
{
	/**
	 * @var Formats
	 */
	private $primary;
	public function setPrimary(Formats $formats) { $this->primary = $formats; }
	public function getPrimary() { return $this->primary; }

	/**
	 * @var ExtraFormats
	 */
	private $extra;
	public function setExtra(ExtraFormats $formats = null) { $this->extra = $formats; }
	public function getExtra() { return $this->extra; }
}
