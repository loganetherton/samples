<?php
namespace ValuePad\DAL\Payment\Types;
use ValuePad\Core\Payment\Enums\AccountType;
use ValuePad\DAL\Support\EnumType;

class AccountTypeType extends EnumType
{
    /**
     * @return string
     */
    protected function getEnumClass()
    {
        return AccountType::class;
    }
}
