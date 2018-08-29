<?php
namespace ValuePad\Core\JobType\Properties;

trait JobTypePersistablePropertyTrait
{
	/**
	 * @var int
	 */
	private $jobType;

	/**
	 * @param int $id
	 */
	public function setJobType($id)
	{
		$this->jobType = $id;
	}

	/**
	 * @return int
	 */
	public function getJobType()
	{
		return $this->jobType;
	}
}
