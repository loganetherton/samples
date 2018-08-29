<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\IntegerCast;
use Ascope\Libraries\Validation\Rules\Obligate;

class ChangePrimaryLicenseProcessor extends AbstractProcessor
{
	protected function rules(Binder $binder)
	{
		$binder->bind('license', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new IntegerCast());
		});
	}

	public function getLicense()
	{
		return (int) $this->get('license');
	}
}
