<?php
namespace ValuePad\Api\Appraisal\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Entities\Document;

class DocumentTransformer extends BaseTransformer
{
	/**
	 * @param Document $document
	 * @return array
	 */
	public function transform($document)
	{
		return $this->extract($document);
	}
}
