<?php
namespace ValuePad\Core\Appraisal\Enums\Property;

use Ascope\Libraries\Enum\EnumCollection;

class ApproachesToBeIncluded extends EnumCollection
{
	/**
	 * @return string
	 */
	public function getEnumClass()
	{
		return ApproachToBeIncluded::class;
	}
}
