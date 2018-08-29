<?php
namespace ValuePad\DAL\Appraisal\Types;

use ValuePad\Core\Appraisal\Enums\Property\OwnerInterest;
use ValuePad\Core\Appraisal\Enums\Property\OwnerInterests;
use ValuePad\DAL\Support\EnumType;

class OwnerInterestsType extends EnumType
{
    /**
     * @return string
     */
    protected function getEnumCollectionClass()
    {
        return OwnerInterests::class;
    }

    /**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return OwnerInterest::class;
	}
}
