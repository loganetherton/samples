<?php
namespace ValuePad\Core\Appraiser\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Services\AppraiserService;

class JobTypeAvailableForDefaultFee extends AbstractRule
{
	/**
	 * @var AppraiserService
	 */
	private $appraiserService;

	/**
	 * @var Appraiser $appraiser
	 */
	private $appraiser;

	/**
	 * @param AppraiserService $appraiserService
	 * @param Appraiser $appraiser
	 */
	public function __construct(AppraiserService $appraiserService, Appraiser $appraiser)
	{
		$this->appraiserService = $appraiserService;
		$this->appraiser = $appraiser;

		$this->setIdentifier('already-taken');
		$this->setMessage('A default fee has been already set for the provided job type.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if ($this->appraiserService->hasDefaultFeeWithJobType($this->appraiser->getId(), $value)){
			return $this->getError();
		}

		return null;
	}
}
