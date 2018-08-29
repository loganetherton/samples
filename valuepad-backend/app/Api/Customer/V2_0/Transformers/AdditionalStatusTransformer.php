<?php
namespace ValuePad\Api\Customer\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;

class AdditionalStatusTransformer extends BaseTransformer
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
