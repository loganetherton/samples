<?php
namespace ValuePad\Api\Customer\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Customer\Entities\DocumentSupportedFormats;

class DocumentSupportedFormatsTransformer extends BaseTransformer
{
	/**
	 * @param DocumentSupportedFormats $formats
	 * @return array
	 */
	public function transform($formats)
	{
		return $this->extract($formats);
	}
}
