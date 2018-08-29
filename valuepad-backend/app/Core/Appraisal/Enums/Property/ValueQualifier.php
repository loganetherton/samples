<?php
namespace ValuePad\Core\Appraisal\Enums\Property;

use Ascope\Libraries\Enum\Enum;

class ValueQualifier extends Enum
{
	const AS_IS = 'as-is';
	const AS_PROPOSED = 'as-proposed';
	const AS_COMPLETE = 'as-complete';
	const AS_STABILIZED = 'as-stabilized';
	const GOING_CONCERN = 'going-concern';
	const LIQUIDATION_FORCED = 'liquidation-forced';
	const LIQUIDATION_ORDERLY = 'liquidation-orderly';
}
