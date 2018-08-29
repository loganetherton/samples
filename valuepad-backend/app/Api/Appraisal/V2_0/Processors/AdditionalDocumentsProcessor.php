<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;

use ValuePad\Api\Appraisal\V2_0\Support\AdditionalDocumentsConfigurationTrait;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Appraisal\Persistables\AdditionalDocumentPersistable;

class AdditionalDocumentsProcessor extends BaseProcessor
{
	use AdditionalDocumentsConfigurationTrait;

	/**
	 * @return array
	 */
	protected function configuration()
	{
		return $this->getAdditionalDocumentsConfiguration();
	}

	/**
	 * @return AdditionalDocumentPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new AdditionalDocumentPersistable());
	}
}
