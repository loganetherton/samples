<?php
namespace ValuePad\Api\Appraisal\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Entities\Bid;

class BidTransformer extends BaseTransformer
{
	/**
	 * @param Bid $bid
	 * @return array
	 */
	public function transform($bid)
	{
		return $this->extract($bid);
	}
}
