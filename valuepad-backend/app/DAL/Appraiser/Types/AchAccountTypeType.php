<?php
namespace ValuePad\DAL\Appraiser\Types;

use ValuePad\Core\Appraiser\Enums\AchAccountType;
use ValuePad\DAL\Support\EnumType;

class AchAccountTypeType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return AchAccountType::class;
	}
}
