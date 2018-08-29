<?php
namespace ValuePad\Core\Asc\Enums;

use Ascope\Libraries\Enum\EnumCollection;

class Certifications extends EnumCollection
{
	/**
	 * @return string
	 */
	public function getEnumClass()
	{
		return Certification::class;
	}
}
