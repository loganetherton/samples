<?php
namespace ValuePad\Core\User\Validation\Inflators;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Email;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;

class EmailInflator
{
    /**
     * @param Property $property
     */
    public function __invoke(Property $property)
    {
        $property
            ->addRule(new Obligate())
            ->addRule(new Blank())
            ->addRule(new Length(1, 255))
            ->addRule(new Email());
    }
}
