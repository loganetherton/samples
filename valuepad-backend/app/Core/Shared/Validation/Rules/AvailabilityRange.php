<?php
namespace ValuePad\Core\Shared\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;

class AvailabilityRange extends AbstractRule
{
	public function __construct()
	{
		$this->setIdentifier('invalid');
		$this->setMessage('The away date must be before the return date.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		list($from, $to) = $value->extract();

		if ($from >= $to){
			return $this->getError();
		}

		return null;
	}
}
