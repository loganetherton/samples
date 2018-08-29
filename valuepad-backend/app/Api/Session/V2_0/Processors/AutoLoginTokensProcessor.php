<?php
namespace ValuePad\Api\Session\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\IntegerCast;
use Ascope\Libraries\Validation\Rules\Obligate;

class AutoLoginTokensProcessor extends AbstractProcessor
{
	protected function rules(Binder $binder)
	{
		$binder->bind('user', function(Property $property){
			$property->addRule(new Obligate())->addRule(new IntegerCast());
		});
	}

	protected function allowable()
	{
		return ['user'];
	}

	/**
	 * @return int
	 */
	public function getUser()
	{
		return (int) $this->get('user');
	}
}
