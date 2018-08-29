<?php
namespace ValuePad\DAL\User\Types;
use ValuePad\Core\User\Enums\Status;
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
