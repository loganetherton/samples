<?php
namespace ValuePad\Api\Support\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;

class TraversableCast extends AbstractRule
{
	public function __construct()
	{
		$this->setIdentifier('cast');
		$this->setMessage('The value must be traversable.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if (is_traversable($value)){
			return null;
		}

		return $this->getError();
	}
}
