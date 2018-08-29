<?php
namespace ValuePad\Core\Appraisal\Enums\Property;

use Ascope\Libraries\Enum\EnumCollection;

class ValueTypes extends EnumCollection
{
	/**
	 * @return string
	 */
	public function getEnumClass()
	{
		return ValueType::class;
	}
}
