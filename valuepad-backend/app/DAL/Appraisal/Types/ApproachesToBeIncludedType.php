<?php
namespace ValuePad\DAL\Appraisal\Types;

use ValuePad\Core\Appraisal\Enums\Property\ApproachesToBeIncluded;
use ValuePad\Core\Appraisal\Enums\Property\ApproachToBeIncluded;
use ValuePad\DAL\Support\EnumType;

class ApproachesToBeIncludedType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return ApproachToBeIncluded::class;
	}

	/**
	 * @return string
	 */
	protected function getEnumCollectionClass()
	{
		return ApproachesToBeIncluded::class;
	}
}
