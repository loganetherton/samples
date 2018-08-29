<?php
namespace ValuePad\DAL\Appraiser\Types;

use ValuePad\Core\Appraiser\Enums\CommercialExpertise;
use ValuePad\Core\Appraiser\Enums\CommercialExpertiseCollection;
use ValuePad\DAL\Support\EnumType;

class CommercialExpertiseType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return CommercialExpertise::class;
	}

	protected function getEnumCollectionClass()
	{
		return CommercialExpertiseCollection::class;
	}
}
