<?php
namespace ValuePad\DAL\Customer\Types;

use ValuePad\Core\Customer\Enums\Format;
use ValuePad\Core\Customer\Enums\Formats;
use ValuePad\DAL\Support\EnumType;

class FormatsType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return Format::class;
	}

	protected function getEnumCollectionClass()
	{
		return Formats::class;
	}
}
