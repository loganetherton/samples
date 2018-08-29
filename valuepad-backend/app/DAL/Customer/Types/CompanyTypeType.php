<?php
namespace ValuePad\DAL\Customer\Types;

use ValuePad\Core\Customer\Enums\CompanyType;
use ValuePad\DAL\Support\EnumType;

/**
 * @author Tushar Ambalia <tusharambalia17@gmail.com>
 */
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