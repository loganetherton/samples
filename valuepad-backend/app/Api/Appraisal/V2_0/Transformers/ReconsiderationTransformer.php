<?php
namespace ValuePad\Api\Appraisal\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Entities\Reconsideration;

class ReconsiderationTransformer extends BaseTransformer
{
	/**
	 * @param Reconsideration $reconsideration
	 * @return array
	 */
	public function transform($reconsideration)
	{
		return $this->extract($reconsideration);
	}
}
