<?php
namespace ValuePad\Api\Appraiser\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Rules\Moment;
use DateTime;
use ValuePad\Core\Support\Criteria\Day;

class CalendarSearchableProcessor extends AbstractProcessor
{
	/**
	 * @return DateTime
	 */
	public function getFrom()
	{
		$datetime = $this->get('from');

		if ((new Moment('Y-m-d'))->check($datetime)){
			return null;
		}

		return new Day($datetime);
	}

	/**
	 * @return DateTime
	 */
	public function getTo()
	{
		$datetime = $this->get('to');

		if ((new Moment('Y-m-d'))->check($datetime)){
			return null;
		}

		return new Day($datetime);
	}
}
