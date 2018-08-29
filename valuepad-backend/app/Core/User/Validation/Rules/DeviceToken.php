<?php
namespace ValuePad\Core\User\Validation\Rules;
use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\User\Enums\Platform;
use ValuePad\Core\User\Interfaces\DevicePreferenceInterface;

class DeviceToken extends AbstractRule
{
    /**
     * @var DevicePreferenceInterface
     */
    private $preference;

    /**
     * @param DevicePreferenceInterface $preference
     */
    public function __construct(DevicePreferenceInterface $preference)
    {
        $this->preference = $preference;

        $this->setMessage('The provided token is not supported by the specified platform.');
        $this->setIdentifier('invalid');
    }

    /**
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        /**
         * @var Platform $platform
         */
        list($token, $platform) = $value->extract();

        return $this->preference->supports($token, $platform) ? null : $this->getError();
    }
}
