<?php
namespace ValuePad\Core\Appraisal\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use ValuePad\Core\Customer\Enums\Format;
use ValuePad\Core\Customer\Enums\Formats;
use ValuePad\Core\Document\Persistables\Identifier;
use ValuePad\Core\Document\Persistables\Identifiers;
use ValuePad\Core\Document\Services\DocumentService as SourceService;

class PrimaryDocumentFormat extends AbstractRule
{
	/**
	 * @var SourceService
	 */
	private $sourceService;

	/**
	 * @var Formats
	 */
	private $supportedFormats;

	/**
	 * @param SourceService $sourceService
	 */
	public function __construct(SourceService $sourceService)
	{
		$this->sourceService = $sourceService;

		$this->setIdentifier('format');
		$this->setSupportedFormats(new Formats(Format::toObjects()));
	}

	/**
	 * @param Identifier|Identifiers $value
	 * @return Error
	 */
	public function check($value)
	{
		if ($value instanceof Identifier){
			$ids = [$value->getId()];
		} else {
			$ids = $value->getIds();
		}

		$sources = $this->sourceService->getAllSelected($ids);

		foreach ($sources as $source){
			if (!$this->supportedFormats->has(new Format((string) $source->getFormat()))){
				return $this->getError();
			}
		}

		return null;
	}

	/**
	 * @param Formats $formats
	 * @return $this
	 */
	public function setSupportedFormats(Formats $formats)
	{
		$this->supportedFormats = $formats;
		$this->setMessage('The primary document(s) must be in one of the following formats: '.implode(', ', $formats->toArray()));
		return $this;
	}
}
