<?php
namespace ValuePad\Api\Appraiser\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraiser\Entities\Ach;

class AchTransformer extends BaseTransformer
{
	/**
	 * @param Ach $item
	 * @return array
	 */
	public function transform($item)
	{
		return $this->extract($item);
	}
}
