<?php
namespace ValuePad\Core\Appraisal\Enums;

use Ascope\Libraries\Enum\Enum;

class BadgeType extends Enum
{
	const FRESH = 'new';
	const REQUEST_FOR_BID = 'request-for-bid';
	const INSPECTION_SCHEDULED = 'inspection-scheduled';
	const DUE = 'due';
}
