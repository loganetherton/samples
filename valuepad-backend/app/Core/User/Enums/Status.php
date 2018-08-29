<?php
namespace ValuePad\Core\User\Enums;
use Ascope\Libraries\Enum\Enum;

class Status extends Enum
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const DECLINED = 'declined';
    const DISABLED = 'disabled';
}
