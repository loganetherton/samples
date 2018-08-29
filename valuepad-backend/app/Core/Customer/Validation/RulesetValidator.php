<?php
namespace ValuePad\Core\Customer\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Customer\Validation\Rules\AllowedRulesInRuleset;
use ValuePad\Core\Customer\Validation\Rules\RuleValuesCast;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Support\Service\ContainerInterface;

class RulesetValidator extends AbstractThrowableValidator
{
    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->stateService = $container->get(StateService::class);
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('level', function(Property $property){
            $property->addRule(new Obligate());
        });

        $binder->bind('label', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank())
                ->addRule(new Length(1, 255));
        });

        $binder->bind('rules', function(Property $property){
            $property
                ->addRule(new AllowedRulesInRuleset());

            $property
                ->addRule(new RuleValuesCast($this->stateService));
        });
    }
}
