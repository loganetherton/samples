<?php
namespace ValuePad\Core\JobType\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\JobType\Services\JobTypeService;

class JobTypeExists extends AbstractRule
{
	/**
	 * @var JobTypeService
	 */
	private $jobTypeService;

	/**
	 * @param JobTypeService $jobTypeService
	 */
	public function __construct(JobTypeService $jobTypeService)
	{
		$this->jobTypeService = $jobTypeService;

		$this->setIdentifier('exists');
		$this->setMessage('The job type does not exist.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if (!$this->jobTypeService->exists($value)){
			return $this->getError();
		}

		return null;
	}
}
