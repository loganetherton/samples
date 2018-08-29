<?php
namespace ValuePad\Core\Appraiser\Enums;

use Ascope\Libraries\Enum\EnumCollection;

class CommercialExpertiseCollection extends EnumCollection
{
	/**
	 * @return string
	 */
	public function getEnumClass()
	{
		return CommercialExpertise::class;
	}
}
