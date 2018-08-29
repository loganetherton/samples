<?php
namespace ValuePad\Core\JobType\Properties;

use ValuePad\Core\JobType\Entities\JobType;

trait JobTypePropertyTrait
{
	/**
	 * @var JobType
	 */
	private $jobType;

	/**
	 * @param JobType $jobType
	 */
	public function setJobType(JobType $jobType)
	{
		$this->jobType = $jobType;
	}

	/**
	 * @return JobType
	 */
	public function getJobType()
	{
		return $this->jobType;
	}
}
