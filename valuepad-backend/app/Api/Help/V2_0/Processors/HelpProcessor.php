<?php
namespace ValuePad\Api\Help\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\StringCast;

class HelpProcessor extends AbstractProcessor
{
	protected function rules(Binder $binder)
	{
		$binder->bind('description', function(Property $property){
			$property
				->addRule(new StringCast())
				->addRule(new Obligate());
		});
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->get('description');
	}
}
