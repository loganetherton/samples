<?php
namespace ValuePad\Core\Location\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Location\Services\StateService;

class CountyExistsInState extends AbstractRule
{
	/**
	 * @var StateService
	 */
	private $stateService;

	/**
	 * @var State
	 */
	private $currentState;

	public function __construct(StateService $stateService, State $currentState = null)
	{
		$this->stateService = $stateService;
		$this->currentState = $currentState;

		$this->setIdentifier('exists');
		$this->setMessage('The provided county does not belong to the provided state.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if ($value instanceof  Value){
			list($county, $state) = $value->extract();
		} else {
			$county = $value;
			$state = $this->currentState->getCode();
		}

		if (!$this->stateService->hasCounty($state, $county)){
			return $this->getError();
		}

		return null;
	}
}
