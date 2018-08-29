<?php
namespace ValuePad\Api\Customer\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Customer\Entities\JobType;

class JobTypeTransformer extends BaseTransformer
{
	/**
	 * @param JobType $jobType
	 * @return array
	 */
	public function transform($jobType)
	{
		return $this->extract($jobType);
	}
}
