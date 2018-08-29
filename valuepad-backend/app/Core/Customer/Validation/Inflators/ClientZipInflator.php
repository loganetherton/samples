<?php
namespace ValuePad\Core\Customer\Validation\Inflators;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Length;

class ClientZipInflator
{
    /**
     * @param Property $property
     */
    public function __invoke(Property $property)
    {
        $property->addRule(new Length(0, 255));
    }
}
