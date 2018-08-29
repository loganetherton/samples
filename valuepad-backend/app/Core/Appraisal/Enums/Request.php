<?php
namespace ValuePad\Core\Appraisal\Enums;

use Ascope\Libraries\Enum\Enum;

class Request extends Enum
{
	const FEE_INCREASE = 'fee-increase';
	const DUE_DATE_EXTENSION = 'due-date-extension';
	const FEE_INCREASE_AND_DUE_DATE_EXTENSION = 'fee-increase-and-due-date-extension';
	const OTHER = 'other';
}
