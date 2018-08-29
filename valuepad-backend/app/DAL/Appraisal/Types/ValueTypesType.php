<?php
namespace ValuePad\DAL\Appraisal\Types;

use ValuePad\Core\Appraisal\Enums\Property\ValueType;
use ValuePad\Core\Appraisal\Enums\Property\ValueTypes;
use ValuePad\DAL\Support\EnumType;

class ValueTypesType extends EnumType
{
	protected function getEnumCollectionClass()
	{
		return ValueTypes::class;
	}

	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return ValueType::class;
	}
}
