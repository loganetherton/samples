<?php
namespace ValuePad\DAL\User\Types;

use ValuePad\Core\User\Enums\Platform;
use ValuePad\DAL\Support\EnumType;

class PlatformType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return Platform::class;
	}
}
