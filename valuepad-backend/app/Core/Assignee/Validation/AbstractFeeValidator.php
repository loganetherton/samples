<?php
namespace ValuePad\Core\Assignee\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;

abstract class AbstractFeeValidator extends AbstractThrowableValidator
{
    /**
     * @param Binder $binder
     */
    protected function defineAmount(Binder $binder)
    {
        $binder->bind('amount', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Greater(0));
        });
    }
}
