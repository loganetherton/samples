<?php
namespace ValuePad\Api\Customer\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use DateTime;
use ValuePad\Support\Shortcut;

class AwardOrderProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'assignedAt' => 'datetime'
		];
	}

	/**
	 * @return DateTime|null
	 */
	public function getAssignedAt()
	{
		if ($assignedAt = $this->get('assignedAt')){
			return Shortcut::utc($assignedAt);
		}

		return null;
	}
}
