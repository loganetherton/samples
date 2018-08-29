<?php
namespace ValuePad\Api\Help\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\StringCast;

class PasswordChangeProcessor extends AbstractProcessor
{
	/**
	 * @param Binder $binder
	 */
	protected function rules(Binder $binder)
	{
		$binder->bind('token', function(Property $property){
			$property->addRule(new Obligate())->addRule(new StringCast());
		});

		$binder->bind('password', function(Property $property){
			$property->addRule(new Obligate())->addRule(new StringCast());
		});
	}

	/**
	 * @return array
	 */
	protected function allowable()
	{
		return ['token', 'password'];
	}

	/**
	 * @return string
	 */
	public function getToken()
	{
		return $this->get('token');
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->get('password');
	}
}
