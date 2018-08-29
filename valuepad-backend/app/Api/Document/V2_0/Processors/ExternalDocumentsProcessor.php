<?php
namespace ValuePad\Api\Document\V2_0\Processors;

use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Document\Enums\Format;
use ValuePad\Core\Document\Persistables\ExternalDocumentPersistable;

class ExternalDocumentsProcessor extends BaseProcessor
{
	/**
	 * @return array
	 */
	protected function configuration()
	{
		return [
			'name' => 'string',
			'size' => 'int',
			'format' => new Enum(Format::class),
			'url' => 'string'
		];
	}

	/**
	 * @return ExternalDocumentPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new ExternalDocumentPersistable());
	}
}
