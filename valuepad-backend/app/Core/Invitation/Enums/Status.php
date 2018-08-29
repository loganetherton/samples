<?php
namespace ValuePad\Core\Invitation\Enums;

use Ascope\Libraries\Enum\Enum;

class Status extends Enum
{
	const PENDING = 'pending';
	const ACCEPTED = 'accepted';
	const DECLINED = 'declined';
}
