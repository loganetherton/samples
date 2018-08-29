<?php
namespace ValuePad\Core\Appraiser\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Services\AppraiserService;

class StateUnique extends AbstractRule
{
	/**
	 * @var AppraiserService
	 */
	private $appraiserService;

	/**
	 * @var Appraiser
	 */
	private $currentAppraiser;

	/**
	 * @param AppraiserService $appraiserService
	 * @param Appraiser $appraiser
	 */
	public function __construct(AppraiserService $appraiserService, Appraiser $appraiser)
	{
		$this->appraiserService = $appraiserService;
		$this->currentAppraiser = $appraiser;

		$this->setIdentifier('unique');
		$this->setMessage('The license has been added already for the specified state.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if ($this->appraiserService->hasLicenseInState($this->currentAppraiser->getId(), $value)){
			return $this->getError();
		}

		return null;
	}
}
