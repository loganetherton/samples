<?php
namespace ValuePad\Core\Document\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;

class ExternalDocumentValidator extends AbstractThrowableValidator
{
	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$binder->bind('name', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank());
		});

		$binder->bind('format', function(Property $property){
			$property
				->addRule(new Obligate());
		});

		$binder->bind('size', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Greater(0, false));
		});

		$binder->bind('url', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Blank());
		});
	}
}
