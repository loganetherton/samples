<?php
namespace ValuePad\DAL\Appraisal\Types;

use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\DAL\Support\EnumType;

class ProcessStatusType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return ProcessStatus::class;
	}
}
