<?php
namespace ValuePad\Api\Appraiser\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraiser\Entities\DefaultFee;

class DefaultFeeTransformer extends BaseTransformer
{
	/**
	 * @param DefaultFee $fee
	 * @return array
	 */
	public function transform($fee)
	{
		return $this->extract($fee);
	}
}
