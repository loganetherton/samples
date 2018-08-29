<?php
namespace ValuePad\Core\Appraiser\Enums;

use Ascope\Libraries\Enum\Enum;

class CompanyType extends Enum
{
	const INDIVIDUAL_SSN = 'individual-ssn';
	const INDIVIDUAL_TAX_ID = 'individual-tax-id';
	const C_CORPORATION = 'c-corporation';
	const S_CORPORATION = 's-corporation';
	const PARTNERSHIP = 'partnership';
	const TRUST_ESTATE = 'trust-estate';
	const LLC_C = 'llc-c';
	const LLC_S = 'llc-s';
	const LLC_P = 'llc-p';
	const OTHER = 'other';
}
