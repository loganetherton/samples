<?php
namespace ValuePad\DAL\Invitation\Types;

use ValuePad\Core\Invitation\Enums\Requirement;
use ValuePad\Core\Invitation\Enums\Requirements;
use ValuePad\DAL\Support\EnumType;

class RequirementsType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumCollectionClass()
	{
		return Requirements::class;
	}

	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return Requirement::class;
	}
}
