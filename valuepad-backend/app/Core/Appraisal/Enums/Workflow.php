<?php
namespace ValuePad\Core\Appraisal\Enums;

use Ascope\Libraries\Enum\EnumCollection;

class Workflow extends EnumCollection
{
	/**
	 * @return string
	 */
	public function getEnumClass()
	{
		return ProcessStatus::class;
	}
}
