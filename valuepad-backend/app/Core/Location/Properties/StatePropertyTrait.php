<?php
namespace ValuePad\Core\Location\Properties;

use ValuePad\Core\Location\Entities\State;

trait StatePropertyTrait
{
    /**
     * @var State
     */
    private $state;

	/**
	 * @param State $state
	 */
	public function setState(State $state = null)
	{
		$this->state = $state;
	}

	/**
	 * @return State
	 */
	public function getState()
	{
		return $this->state;
	}
}
