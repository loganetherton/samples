<?php
namespace ValuePad\Core\Invitation\Enums;

use Ascope\Libraries\Enum\EnumCollection;

class Requirements extends EnumCollection
{
	/**
	 * @return string
	 */
	public function getEnumClass()
	{
		return Requirement::class;
	}
}
