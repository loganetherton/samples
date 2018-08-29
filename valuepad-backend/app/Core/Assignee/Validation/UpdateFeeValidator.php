<?php
namespace ValuePad\Core\Assignee\Validation;

use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\ReadOnly;

class UpdateFeeValidator extends AbstractFeeValidator
{
    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('jobType', function(Property $property){
            $property
                ->addRule(new ReadOnly());
        });

        $this->defineAmount($binder);
    }

    /**
     * @param array|object $source
     * @param bool $soft
     */
    public function validate($source, $soft = false)
    {
        parent::validate($source, true);
    }
}
