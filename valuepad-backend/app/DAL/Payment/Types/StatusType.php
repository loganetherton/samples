<?php
namespace ValuePad\DAL\Payment\Types;
use ValuePad\Core\Payment\Enums\Status;
use ValuePad\DAL\Support\EnumType;

class StatusType extends EnumType
{
    /**
     * @return string
     */
    protected function getEnumClass()
    {
        return Status::class;
    }
}
