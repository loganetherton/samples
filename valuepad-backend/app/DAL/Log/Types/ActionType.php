<?php
namespace ValuePad\DAL\Log\Types;

use ValuePad\Core\Log\Enums\Action;
use ValuePad\DAL\Support\EnumType;

class ActionType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return Action::class;
	}
}
