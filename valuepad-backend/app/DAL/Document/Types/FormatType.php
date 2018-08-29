<?php
namespace ValuePad\DAL\Document\Types;

use ValuePad\Core\Document\Enums\Format;
use ValuePad\DAL\Support\EnumType;

class FormatType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return Format::class;
	}
}
