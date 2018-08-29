<?php
namespace ValuePad\Core\Appraiser\Enums;

use Ascope\Libraries\Enum\EnumCollection;

class BusinessTypes extends EnumCollection
{
	/**
	 * @return string
	 */
	public function getEnumClass()
	{
		return BusinessType::class;
	}
}
