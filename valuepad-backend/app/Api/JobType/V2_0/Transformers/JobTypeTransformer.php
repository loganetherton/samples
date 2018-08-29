<?php
namespace ValuePad\Api\JobType\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\JobType\Entities\JobType;

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
