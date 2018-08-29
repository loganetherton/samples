<?php
namespace ValuePad\Core\Appraisal\Enums\Property;

use Ascope\Libraries\Enum\Enum;

class OwnerInterest extends Enum
{
	const AIR_RIGHTS = 'air-rights';
	const FEE_SIMPLE = 'fee-simple';
	const LEASED_FEE = 'leased-fee';
	const DUPLEX = 'duplex';
	const LEASEHOLD = 'leasehold';
	const PARTIAL_INTEREST = 'partial-interest';
}
