<?php
namespace ValuePad\Core\Payment\Enums;
use Ascope\Libraries\Enum\Enum;

class Means extends Enum
{
    const BANK_ACCOUNT = 'bank-account';
    const CREDIT_CARD = 'credit-card';
}
