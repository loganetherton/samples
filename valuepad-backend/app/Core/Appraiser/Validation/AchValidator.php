<?php
namespace ValuePad\Core\Appraiser\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use ValuePad\Core\Appraiser\Validation\Definers\AchDefiner;

class AchValidator extends AbstractThrowableValidator
{
	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
        $definer = new AchDefiner();
        $definer->define($binder);
	}
}
