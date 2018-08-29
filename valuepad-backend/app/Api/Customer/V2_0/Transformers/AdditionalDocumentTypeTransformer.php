<?php
namespace ValuePad\Api\Customer\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Customer\Entities\AdditionalDocumentType;

class AdditionalDocumentTypeTransformer extends BaseTransformer
{
	/**
	 * @param AdditionalDocumentType $type
	 * @return array
	 */
	public function transform($type)
	{
		return $this->extract($type);
	}
}
