<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Moment;
use Ascope\Libraries\Validation\Rules\Obligate;
use DateTime;
use ValuePad\Support\Shortcut;

class ScheduleInspectionProcessor extends AbstractProcessor
{
	protected function rules(Binder $binder)
	{
		$binder->bind('scheduledAt', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Moment());
		});

		$binder->bind('estimatedCompletionDate', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Moment());
		});
	}

	protected function allowable()
	{
		return ['scheduledAt', 'estimatedCompletionDate'];
	}

	/**
	 * @return DateTime
	 */
	public function getScheduledAt()
	{
		return Shortcut::utc($this->get('scheduledAt'));
	}

	/**
	 * @return DateTime
	 */
	public function getEstimatedCompletionDate()
	{
		return Shortcut::utc($this->get('estimatedCompletionDate'));
	}
}
