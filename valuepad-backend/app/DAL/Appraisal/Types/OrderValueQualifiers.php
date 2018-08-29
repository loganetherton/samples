<?php
namespace ValuePad\DAL\Appraisal\Types;
use ValuePad\Core\Appraisal\Enums\ValueQualifiers;
use ValuePad\Core\Appraisal\Enums\ValueQualifier;
use ValuePad\DAL\Support\EnumType;

class OrderValueQualifiers extends EnumType
{
    /**
     * @return string
     */
    protected function getEnumClass()
    {
        return ValueQualifier::class;
    }

    /**
     * @return string
     */
    protected function getEnumCollectionClass()
    {
        return ValueQualifiers::class;
    }
}
