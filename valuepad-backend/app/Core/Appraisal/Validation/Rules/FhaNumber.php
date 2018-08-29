<?php
namespace ValuePad\Core\Appraisal\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Rules\Regex;
use Ascope\Libraries\Validation\Value;

class FhaNumber extends AbstractRule
{
    public function __construct()
    {
        $this->setIdentifier('format')
            ->setMessage('The FHA number must contain only letters, digits, and dashes.');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        $error = (new Regex('/^[a-zA-Z0-9\-]+$/'))->check($value);

        if ($error) {
            return $this->getError();
        }

        return null;
    }
}
