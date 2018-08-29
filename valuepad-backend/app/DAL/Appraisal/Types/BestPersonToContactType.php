<?php
namespace ValuePad\DAL\Appraisal\Types;

use ValuePad\Core\Appraisal\Enums\Property\BestPersonToContact;
use ValuePad\DAL\Support\EnumType;

class BestPersonToContactType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return BestPersonToContact::class;
	}
}
