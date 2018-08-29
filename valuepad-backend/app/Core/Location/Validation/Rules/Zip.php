<?php
namespace ValuePad\Core\Location\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Rules\Regex;
use Ascope\Libraries\Validation\Value;

/**
 *
 *
 */
class Zip extends AbstractRule
{
    public function __construct()
    {
        $this->setIdentifier('format');
        $this->setMessage('The zip code must be 5 digits long.');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        $error = (new Regex('/^([0-9]{5})|([0-9]{5}\-[0-9]{4})$/'))->check($value);

        if ($error) {
            return $this->getError();
        }

        return null;
    }
}
