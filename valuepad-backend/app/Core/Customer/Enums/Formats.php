<?php
namespace ValuePad\Core\Customer\Enums;

use Ascope\Libraries\Enum\EnumCollection;

class Formats extends EnumCollection
{
	/**
	 * @return string
	 */
	public function getEnumClass()
	{
		return Format::class;
	}
}
