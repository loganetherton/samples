<?php
namespace ValuePad\Core\Appraisal\Enums\Property;

use Ascope\Libraries\Enum\EnumCollection;

class ValueQualifiers extends EnumCollection
{
	/**
	 * @return string
	 */
	public function getEnumClass()
	{
		return ValueQualifier::class;
	}
}
