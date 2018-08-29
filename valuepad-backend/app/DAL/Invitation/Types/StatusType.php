<?php
namespace ValuePad\DAL\Invitation\Types;

use ValuePad\Core\Invitation\Enums\Status;
use ValuePad\DAL\Support\EnumType;

class StatusType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return Status::class;
	}
}
