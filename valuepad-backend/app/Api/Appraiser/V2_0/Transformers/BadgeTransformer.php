<?php
namespace ValuePad\Api\Appraiser\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Objects\Badge;

class BadgeTransformer extends BaseTransformer
{
	/**
	 * @param Badge $badge
	 * @return array
	 */
	public function transform($badge)
	{
		return $this->extract($badge);
	}
}
