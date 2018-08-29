<?php
namespace ValuePad\Core\Payment\Enums;
use Ascope\Libraries\Enum\Enum;

class AccountType extends Enum
{
    const CHECKING = 'checking';
    const SAVINGS = 'savings';
    const BUSINESS_CHECKING = 'business-checking';
}
