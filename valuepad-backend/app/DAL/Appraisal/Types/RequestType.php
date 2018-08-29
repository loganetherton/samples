<?php
namespace ValuePad\DAL\Appraisal\Types;

use ValuePad\Core\Appraisal\Enums\Request;
use ValuePad\DAL\Support\EnumType;

class RequestType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return Request::class;
	}
}
