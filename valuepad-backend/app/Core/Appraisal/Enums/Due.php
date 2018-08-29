<?php
namespace ValuePad\Core\Appraisal\Enums;

use Ascope\Libraries\Enum\Enum;

class Due extends Enum
{
	const TODAY = 'today';
	const TOMORROW = 'tomorrow';
	const NEXT_7_DAYS = 'next-7-days';
}
