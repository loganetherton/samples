<?php
namespace ValuePad\Core\Appraisal\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;

class ConditionsValidator extends AbstractThrowableValidator
{
	use ConditionsValidatorTrait;

	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$this->defineConditions($binder);
	}
}
