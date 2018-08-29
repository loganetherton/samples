<?php
namespace ValuePad\DAL\Appraisal\Types;

use ValuePad\Core\Appraisal\Enums\Property\ValueQualifier;
use ValuePad\Core\Appraisal\Enums\Property\ValueQualifiers;
use ValuePad\DAL\Support\EnumType;

class ValueQualifiersType extends EnumType
{
	protected function getEnumCollectionClass()
	{
		return ValueQualifiers::class;
	}

	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return ValueQualifier::class;
	}
}
