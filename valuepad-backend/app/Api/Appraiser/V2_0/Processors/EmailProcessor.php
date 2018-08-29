<?php
namespace ValuePad\Api\Appraiser\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\StringCast;

class EmailProcessor extends AbstractProcessor
{
	/**
	 * @param Binder $binder
	 */
	protected function rules(Binder $binder)
	{
		$binder->bind('email', function(Property $property){
			$property
				->addRule(new StringCast())
				->addRule(new Obligate());
		});
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->get('email');
	}
}
