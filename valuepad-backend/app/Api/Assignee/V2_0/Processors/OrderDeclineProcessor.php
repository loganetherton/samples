<?php
namespace ValuePad\Api\Assignee\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Enum;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\StringCast;
use ValuePad\Core\Appraisal\Enums\DeclineReason;

class OrderDeclineProcessor extends AbstractProcessor
{
	/**
	 * @param Binder $binder
	 */
	protected function rules(Binder $binder)
	{
		$binder->bind('reason', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Enum(DeclineReason::class));
		});

		$binder->bind('message', function(Property $property){
			$property
				->addRule(new StringCast());
		});
	}

	protected function allowable()
	{
		return [
			'reason', 'message'
		];
	}

	/**
	 * @return DeclineReason
	 */
	public function getDeclineReason()
	{
		return new DeclineReason($this->get('reason'));
	}

	/**
	 * @return string
	 */
	public function getDeclineMessage()
	{
		return $this->get('message');
	}
}
