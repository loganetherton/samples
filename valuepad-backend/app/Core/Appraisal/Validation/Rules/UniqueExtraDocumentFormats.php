<?php
namespace ValuePad\Core\Appraisal\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Document\Persistables\Identifiers;
use ValuePad\Core\Document\Services\DocumentService as SourceService;

class UniqueExtraDocumentFormats extends AbstractRule
{
	/**
	 * @var SourceService
	 */
	private $sourceService;

	/**
	 * @param SourceService $sourceService
	 */
	public function __construct(SourceService $sourceService)
	{
		$this->setIdentifier('unique');
		$this->setMessage('Only one extra document per format is allowed.');
		$this->sourceService = $sourceService;
	}

	/**
	 * @param mixed|Value|Identifiers $value
	 * @return Error|null
	 */
	public function check($value)
	{
		$sources = $this->sourceService->getAllSelected($value->getIds());

		$formats = [];

		foreach ($sources as $source){
			$value = $source->getFormat()->value();

			if (in_array($value, $formats)){
				return $this->getError();
			}

			$formats[] = $value;
		}

		return null;
	}
}
