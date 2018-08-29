<?php
namespace ValuePad\DAL\User\Types;

use ValuePad\Core\User\Enums\Intent;
use ValuePad\DAL\Support\EnumType;

class IntentType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return Intent::class;
	}
}
