<?php
namespace ValuePad\Core\Appraiser\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Appraiser\Services\AppraiserService;

class AppraiserExists extends AbstractRule
{
	/**
	 * @var AppraiserService
	 */
	private $appraiserService;

	/**
	 * @param AppraiserService $appraiserService
	 */
	public function __construct(AppraiserService $appraiserService)
	{
		$this->appraiserService = $appraiserService;

		$this->setIdentifier('exists');
		$this->setMessage('The appraiser with the provided ID does not exist.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if (!$this->appraiserService->exists($value)){
			return $this->getError();
		}

		return null;
	}
}
