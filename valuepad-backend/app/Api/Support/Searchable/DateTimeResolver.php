<?php
namespace ValuePad\Api\Support\Searchable;

use Ascope\Libraries\Validation\Rules\Moment;
use DateTime;
use ValuePad\Support\Shortcut;

class DateTimeResolver
{
	/**
	 * @param string $datetime
	 * @return bool
	 */
	public function isProcessable($datetime)
	{
		return !(new Moment())->check($datetime);
	}

	/**
	 * @param string $datetime
	 * @return DateTime
	 */
	public function resolve($datetime)
	{
		return Shortcut::utc($datetime);
	}
}
