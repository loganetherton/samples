<?php
namespace ValuePad\Api\Help\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\StringCast;

class PasswordResetProcessor extends AbstractProcessor
{
	protected function rules(Binder $binder)
	{
		$binder->bind('username', function(Property $property){
			$property->addRule(new Obligate())->addRule(new StringCast());
		});
	}

	protected function allowable()
	{
		return ['username'];
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->get('username');
	}
}
