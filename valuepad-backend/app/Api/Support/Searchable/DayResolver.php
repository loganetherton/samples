<?php
namespace ValuePad\Api\Support\Searchable;

use Ascope\Libraries\Validation\Rules\Moment;
use ValuePad\Core\Support\Criteria\Day;

class DayResolver
{
	/**
	 * @param string $date
	 * @return bool
	 */
	public function isProcessable($date)
	{
		return !(new Moment('Y-m-d'))->check($date);
	}

	/**
	 * @param string $date
	 * @return Day
	 */
	public function resolve($date)
	{
		return new Day($date);
	}
}
