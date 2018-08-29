<?php
namespace ValuePad\Core\Customer\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;

class SettingsValidator extends AbstractThrowableValidator
{
	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$binder->bind('pushUrl', function(Property $property){
			$property
				->addRule(new Blank());
		});

		$binder->bind('daysPriorInspectionDate', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Greater(0));
		});

		$binder->bind('daysPriorEstimatedCompletionDate', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Greater(0));
		});

		$binder->bind('preventViolationOfDateRestrictions', function(Property $property){
			$property
				->addRule(new Obligate());
		});

		$binder->bind('disallowChangeJobTypeFees', function(Property $property){
			$property
				->addRule(new Obligate());
		});

		$binder->bind('showClientToAppraiser', function(Property $property){
			$property
				->addRule(new Obligate());
		});

		$binder->bind('showDocumentsToAppraiser', function(Property $property){
			$property
				->addRule(new Obligate());
		});

		$binder->bind('isSmsEnabled', function(Property $property){
			$property
				->addRule(new Obligate());
		});

        $binder->bind('unacceptedReminder', function(Property $property){
            $property->addRule(new Greater(0));
        });

        $binder->bind('removeAccountingData', function(Property $property){
			$property
				->addRule(new Obligate());
		});
	}
}
