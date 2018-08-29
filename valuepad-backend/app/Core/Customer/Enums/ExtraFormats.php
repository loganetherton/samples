<?php
namespace ValuePad\Core\Customer\Enums;

use Ascope\Libraries\Enum\EnumCollection;

class ExtraFormats extends EnumCollection
{
	/**
	 * @return string
	 */
	public function getEnumClass()
	{
		return ExtraFormat::class;
	}
}
