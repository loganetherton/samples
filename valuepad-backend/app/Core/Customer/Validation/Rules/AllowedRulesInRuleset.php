<?php
namespace ValuePad\Core\Customer\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Customer\Enums\Rule;

class AllowedRulesInRuleset extends AbstractRule
{
    public function __construct()
    {
        $this->setMessage('The provided rules must be in the list of the supported rules: '
            .implode(', ', Rule::toArray()));

        $this->setIdentifier('format');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        $keys = array_keys($value);

        $supported = Rule::toArray();

        foreach ($keys as $key){
            if (!in_array($key, $supported, true)){
                return $this->getError();
            }
        }

        return null;
    }
}
