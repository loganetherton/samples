<?php
namespace ValuePad\Core\Location\Validation\Inflators;

use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Rules\StateExists;

class StateInflator
{
	/**
	 * @var StateService
	 */
	private $stateService;

	/**
	 * @var bool
	 */
	private $isRequired = true;

	/**
	 * @param StateService $stateService
	 */
	public function __construct(StateService $stateService)
	{
		$this->stateService = $stateService;
	}

	public function __invoke(Property $property)
	{
		if ($this->isRequired){
			$property->addRule(new Obligate());
		}

		$property
			->addRule(new Length(2, 2))
			->addRule(new StateExists($this->stateService));
	}

	/**
	 * @param bool $flag
	 * @return $this
	 */
	public function setRequired($flag)
	{
		$this->isRequired = $flag;

		return $this;
	}
}
