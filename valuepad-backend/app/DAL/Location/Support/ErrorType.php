<?php
namespace ValuePad\DAL\Location\Support;
use ValuePad\DAL\Support\EnumType;

class ErrorType extends EnumType
{
    /**
     * @return string
     */
    protected function getEnumClass()
    {
        return Error::class;
    }
}
