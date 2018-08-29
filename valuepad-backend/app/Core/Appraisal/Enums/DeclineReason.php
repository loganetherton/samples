<?php
namespace ValuePad\Core\Appraisal\Enums;

use Ascope\Libraries\Enum\Enum;


class DeclineReason extends Enum
{
	const TOO_BUSY = 'too-busy';
	const OUT_OF_COVERAGE_AREA = 'out-of-coverage-area';
	const OTHER = 'other';
}
