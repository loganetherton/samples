<?php
namespace ValuePad\DAL\Customer\Types;

use ValuePad\Core\Customer\Enums\Criticality;
use ValuePad\DAL\Support\EnumType;

class CriticalityType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return Criticality::class;
	}
}
