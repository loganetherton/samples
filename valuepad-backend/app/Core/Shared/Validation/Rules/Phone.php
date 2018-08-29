<?php
namespace ValuePad\Core\Shared\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Rules\Regex;
use Ascope\Libraries\Validation\Value;

/**
 *
 *
 */
class Phone extends AbstractRule
{

    public function __construct()
    {
        $this->setIdentifier('format');
        $this->setMessage('The phone number must be provided in the following format: (xxx) xxx-xxxx');
    }

    /**
     *
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        $error = (new Regex('/^\([0-9]{3}\) [0-9]{3}\-[0-9]{4}$/'))->check($value);

        if ($error) {
            return $this->getError();
        }

        return null;
    }
}
