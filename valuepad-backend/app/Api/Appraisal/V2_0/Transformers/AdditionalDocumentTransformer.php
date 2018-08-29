<?php
namespace ValuePad\Api\Appraisal\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Entities\AdditionalDocument;

class AdditionalDocumentTransformer extends BaseTransformer
{
	/**
	 * @param AdditionalDocument $document
	 * @return array
	 */
	public function transform($document)
	{
		return $this->extract($document);
	}
}
