<?php
namespace ValuePad\Core\Appraiser\Enums;

use Ascope\Libraries\Enum\Enum;

class BusinessType extends Enum
{
	const NOT_APPLICABLE = 'not-applicable';
	const CERTIFIED_MINORITY = 'certified-minority';
	const HUN_ZONE_SMALL_BUSINESS = 'hub-zone-small-business';
	const LARGE_BUSINESS = 'large-business';
	const SMALL_BUSINESS = 'small-business';
	const SMALL_DISADVANTAGED_BUSINESS = 'small-disadvantaged-business';
	const VETERAN_OWNED_BUSINESS = 'veteran-owned-business';
	const WOMEN_OWNED_BUSINESS = 'women-owned-business';
}
