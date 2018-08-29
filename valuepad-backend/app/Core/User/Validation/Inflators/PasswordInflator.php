<?php
namespace ValuePad\Core\User\Validation\Inflators;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\User\Validation\Rules\Password;

class PasswordInflator
{
    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * @param EnvironmentInterface $environment
     */
    public function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param Property $property
     */
    public function __invoke(Property $property)
    {
        $property->addRule(new Obligate());

        if (!$this->environment->isRelaxed()){
            $property->addRule(new Password());
        } else {
            $property->addRule(new Blank());
        }
    }
}
