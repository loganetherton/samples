<?php
namespace ValuePad\Core\Customer\Validation;

use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Appraisal\Validation\MessageValidator as BaseMessageValidator;

class MessageValidator extends BaseMessageValidator
{
	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		parent::define($binder);

		$binder->bind('employee', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank());
		});
	}
}
