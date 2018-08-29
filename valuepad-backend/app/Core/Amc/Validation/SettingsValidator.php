<?php
namespace ValuePad\Core\Amc\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;

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
    }
}
