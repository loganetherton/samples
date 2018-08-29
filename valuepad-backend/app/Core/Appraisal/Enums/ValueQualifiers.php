<?php
namespace ValuePad\Core\Appraisal\Enums;
use Ascope\Libraries\Enum\EnumCollection;

class ValueQualifiers extends EnumCollection
{
    /**
     * @return string
     */
    public function getEnumClass()
    {
        return ValueQualifier::class;
    }
}
