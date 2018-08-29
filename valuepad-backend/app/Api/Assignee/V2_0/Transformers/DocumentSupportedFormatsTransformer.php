<?php
namespace ValuePad\Api\Assignee\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;

class DocumentSupportedFormatsTransformer extends BaseTransformer
{
	/**
	 * @param object $item
	 * @return array
	 */
	public function transform($item)
	{
		return $this->extract($item);
	}
}
