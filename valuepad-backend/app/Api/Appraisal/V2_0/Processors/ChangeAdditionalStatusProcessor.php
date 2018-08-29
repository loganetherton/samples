<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\IntegerCast;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\StringCast;

class ChangeAdditionalStatusProcessor extends AbstractProcessor
{
	protected function rules(Binder $binder)
	{
		$binder->bind('additionalStatus', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new IntegerCast());
		});

		$binder->bind('comment', function(Property $property){
			$property
				->addRule(new StringCast());
		});
	}

	/**
	 * @return int
	 */
	public function getAdditionalStatus()
	{
		return (int) $this->get('additionalStatus');
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->get('comment');
	}
}
