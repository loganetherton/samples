<?php
namespace ValuePad\Core\User\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\User\Interfaces\DevicePreferenceInterface;
use ValuePad\Core\User\Validation\Rules\DeviceToken;

class DeviceValidator extends AbstractThrowableValidator
{
	/**
	 * @var DevicePreferenceInterface
	 */
	private $preference;

	/**
	 * @param DevicePreferenceInterface $preference
	 */
	public function __construct(DevicePreferenceInterface $preference)
	{
		$this->preference = $preference;
	}

	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$binder->bind('token', function(Property $property){
			$property
				->addRule(new Obligate());
		});

		$binder->bind('platform', function(Property $property){
			$property->addRule(new Obligate());
		});

		$binder->bind('token', ['token', 'platform'], function(Property $property){
			$property->addRule(new DeviceToken($this->preference));
		});
	}
}
