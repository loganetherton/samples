<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Customer\Enums\ExtraFormat;
use ValuePad\Core\Customer\Enums\Format;
use ValuePad\Core\Customer\Persistables\DocumentSupportedFormatsPersistable;

class DocumentFormatsProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'jobType' => 'int',
			'primary' => [new Enum(Format::class)],
			'extra' => [new Enum(ExtraFormat::class)]
		];
	}

	/**
	 * @return DocumentSupportedFormatsPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new DocumentSupportedFormatsPersistable());
	}
}
