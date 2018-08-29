<?php
namespace ValuePad\Core\Appraisal\Enums\Property;

use Ascope\Libraries\Enum\Enum;

class Occupancy extends Enum
{
	const OWNER = 'owner';
	const TENANT = 'tenant';
	const NEW_CONSTRUCTION = 'new-construction';
	const UNKNOWN = 'unknown';
}
