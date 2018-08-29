<?php
namespace ValuePad\Core\Payment\Enums;

use Ascope\Libraries\Enum\Enum;

class Status extends Enum
{
	const APPROVED = 'approved';
	const DECLINED = 'declined';
	const ERROR = 'error';
	const PENDING = 'pending';
}
