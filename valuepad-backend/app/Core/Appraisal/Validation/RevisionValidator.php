<?php
namespace ValuePad\Core\Appraisal\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\SourceHandlerInterface;

class RevisionValidator extends AbstractThrowableValidator
{
	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$binder->bind('message', function(Property $property){
			$property->addRule(new Obligate());
		})->when(function(SourceHandlerInterface $source){
			return $source->getValue('checklist') === null;
		});
	}
}
