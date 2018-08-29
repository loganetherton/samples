<?php
namespace ValuePad\Core\Shared\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Rules\NullProcessableRuleInterface;
use Ascope\Libraries\Validation\Value;

class NotNullable extends AbstractRule implements NullProcessableRuleInterface
{
    public function __construct()
    {
        $this->setIdentifier('not-nullable');
        $this->setMessage('The "null" value is not allowed for this field.');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        if ($value === null){
            return $this->getError();
        }

        return null;
    }
}
