<?php
namespace ValuePad\Api\Help\V2_0\Processors;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\StringCast;
use ValuePad\Api\Support\BaseProcessor;

class HintsProcessor extends BaseProcessor
{
    /**
     * @param Binder $binder
     */
    protected function rules(Binder $binder)
    {
        $binder->bind('email', function(Property $property){
            $property
                ->addRule(new StringCast())
                ->addRule(new Blank())
                ->addRule(new Obligate());
        });
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->get('email');
    }
}
