<?php
namespace ValuePad\Core\Log\Extras;

use ValuePad\Core\Location\Entities\State;

class StateExtra extends Extra
{
	/**
	 * @param string $code
	 * @param string $name
	 */
	public function __construct($code, $name)
	{
		$this[Extra::CODE] = $code;
		$this[Extra::NAME] = $name;
	}

	/**
	 * @param State $state
	 * @return StateExtra
	 */
	public static function fromState(State $state)
	{
		return new self($state->getCode(), $state->getName());
	}
}
