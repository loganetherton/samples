<?php
namespace ValuePad\Core\Payment\Validation;

use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Numeric;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Payment\Validation\Rules\CreditCardNotExpired;

class CreditCardValidator extends AbstractPaymentMethodValidator
{
	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
        parent::define($binder);

		$binder->bind('number', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank())
				->addRule(new Numeric())
				->addRule(new Length(13, 16));
		});

		$binder->bind('code', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank())
				->addRule(new Numeric())
				->addRule(new Length(3, 4));
		});

		$binder->bind('expiresAt', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new CreditCardNotExpired());
		});
	}
}
