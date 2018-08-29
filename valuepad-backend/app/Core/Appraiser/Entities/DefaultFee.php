<?php
namespace ValuePad\Core\Appraiser\Entities;

use ValuePad\Core\JobType\Entities\JobType;

class DefaultFee
{
	/**
	 * @var int
	 */
	private $id;
	public function setId($id) { $this->id = $id; }
	public function getId() { return $this->id; }

	/**
	 * @var JobType
	 */
	private $jobType;
	public function setJobType(JobType $jobType) { $this->jobType = $jobType; }
	public function getJobType() { return $this->jobType; }

	/**
	 * @var float
	 */
	private $amount;
	public function setAmount($amount) { $this->amount = $amount; }
	public function getAmount() { return $this->amount; }

	/**
	 * @var Appraiser
	 */
	private $appraiser;
	public function getAppraiser() { return $this->appraiser; }

	/**
	 * @param Appraiser $appraiser
	 */
	public function setAppraiser(Appraiser $appraiser)
	{
		$this->appraiser = $appraiser;
		$this->appraiser->addDefaultFee($this);
	}
}
