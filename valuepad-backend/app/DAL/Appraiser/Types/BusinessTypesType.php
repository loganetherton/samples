<?php
namespace ValuePad\DAL\Appraiser\Types;

use ValuePad\Core\Appraiser\Enums\BusinessType;
use ValuePad\Core\Appraiser\Enums\BusinessTypes;
use ValuePad\DAL\Support\EnumType;

class BusinessTypesType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return BusinessType::class;
	}

	/**
	 * @return string
	 */
	protected function getEnumCollectionClass()
	{
		return BusinessTypes::class;
	}
}
