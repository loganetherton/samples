<?php
namespace ValuePad\Api\Assignee\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Objects\Totals;

class TotalsTransformer extends BaseTransformer
{
	/**
	 * @param Totals[] $totals
	 * @return array
	 */
	public function transform($totals)
	{
		return [
			'paid' => $this->extract($totals['paid']),
			'unpaid' => $this->extract($totals['unpaid'])
		];
	}
}
