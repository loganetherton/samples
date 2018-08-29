<?php
namespace ValuePad\DAL\Appraiser\Types;

use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\DAL\Support\EnumType;

class CompanyTypeType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return CompanyType::class;
	}
}
