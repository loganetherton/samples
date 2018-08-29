<?php
namespace ValuePad\DAL\Appraisal\Types;

use ValuePad\Core\Appraisal\Enums\Property\Occupancy;
use ValuePad\DAL\Support\EnumType;

class OccupancyType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return Occupancy::class;
	}
}
