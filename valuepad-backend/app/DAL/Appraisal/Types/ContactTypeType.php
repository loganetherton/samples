<?php
namespace ValuePad\DAL\Appraisal\Types;

use ValuePad\Core\Appraisal\Enums\Property\ContactType;
use ValuePad\DAL\Support\EnumType;

class ContactTypeType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return ContactType::class;
	}
}
