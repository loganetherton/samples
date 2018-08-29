<?php
namespace ValuePad\Api\Appraisal\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Objects\Conditions;

class ConditionsTransformer extends BaseTransformer
{
	/**
	 * @param Conditions $conditions
	 * @return array
	 */
	public function transform($conditions)
	{
		return $this->extract($conditions);
	}
}
