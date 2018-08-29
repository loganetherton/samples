<?php
namespace ValuePad\Core\Customer\Enums;

use Ascope\Libraries\Enum\Enum;

class Criticality extends Enum
{
	const DISABLED = 'disabled';
	const WARNING = 'warning';
	const HARDSTOP = 'hardstop';
}
