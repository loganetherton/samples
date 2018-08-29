<?php
namespace ValuePad\DAL\Appraisal\Types;

use ValuePad\Core\Appraisal\Enums\ConcessionUnit;
use ValuePad\DAL\Support\EnumType;

class ConcessionUnitType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return ConcessionUnit::class;
	}
}
