<?php
namespace ValuePad\Core\Appraisal\Enums\Property;
use Ascope\Libraries\Enum\EnumCollection;

class OwnerInterests extends EnumCollection
{
    /**
     * @return string
     */
    public function getEnumClass()
    {
        return OwnerInterest::class;
    }
}
