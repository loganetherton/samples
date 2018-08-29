<?php
namespace ValuePad\Api\Support\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Rules\IntegerCast;
use Ascope\Libraries\Validation\Value;

class MonthYearPair extends AbstractRule
{
	public function __construct()
	{
		$this->setIdentifier('cast');
		$this->setMessage('The value must be array consisting of 2 fields.'.
			'Where the "month" and "year" fields are integers and the "month" field cannot be less than 1 and greater than 12.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if (!is_array($value)){
			return $this->getError();
		}

		$month = array_take($value, 'month');
		$year = array_take($value, 'year');

		if ($month === null || $year === null){
			return $this->getError();
		}

		if ((new IntegerCast())->check($month)){
			return $this->getError();
		}

		if ((new IntegerCast())->check($year)){
			return $this->getError();
		}

		if ($month < 1 || $month > 12){
			return $this->getError();
		}

		return null;
	}
}
