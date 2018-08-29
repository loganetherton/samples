<?php
namespace ValuePad\Core\User\Enums;

use Ascope\Libraries\Enum\Enum;

class Intent extends Enum
{
	const RESET_PASSWORD = 'reset-password';
	const AUTO_LOGIN = 'auto-login';
}
