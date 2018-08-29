<?php
namespace ValuePad\Core\Customer\Enums;

use Ascope\Libraries\Enum\Enum;

/**
 * @author Tushar Ambalia <tusharambalia17@gmail.com>
 */
class CompanyType extends Enum
{
	const APPRAISAL_MANAGEMENT_COMPANY = 'appraisal-management-company';
	const BANK_LENDER = 'bank-lender';
	const CREDIT_UNION = 'credit-union';
	const MORTGAGE_BROKER = 'mortgage-broker';
}