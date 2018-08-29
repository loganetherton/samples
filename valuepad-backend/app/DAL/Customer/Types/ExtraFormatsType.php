<?php
namespace ValuePad\DAL\Customer\Types;

use ValuePad\Core\Customer\Enums\ExtraFormat;
use ValuePad\Core\Customer\Enums\ExtraFormats;
use ValuePad\DAL\Support\EnumType;

class ExtraFormatsType extends EnumType
{
	/**
	 * @return string
	 */
	protected function getEnumClass()
	{
		return ExtraFormat::class;
	}

	protected function getEnumCollectionClass()
	{
		return ExtraFormats::class;
	}
}
