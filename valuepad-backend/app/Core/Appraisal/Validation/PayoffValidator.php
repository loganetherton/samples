<?php
namespace ValuePad\Core\Appraisal\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Email;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Numeric;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Definer\LocationDefiner;
use ValuePad\Core\Payment\Validation\Rules\CreditCardNotExpired;

class PayoffValidator extends AbstractThrowableValidator
{
    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @param StateService $stateService
     */
    public function __construct(StateService $stateService)
    {
        $this->stateService = $stateService;
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('creditCard.number', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank())
                ->addRule(new Numeric())
                ->addRule(new Length(13, 16));
        });

        $binder->bind('creditCard.code', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank())
                ->addRule(new Numeric())
                ->addRule(new Length(3, 4));
        });

        $binder->bind('creditCard.expiresAt', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new CreditCardNotExpired());
        });

        $binder->bind('creditCard.firstName', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank());
        });

        $binder->bind('creditCard.lastName', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank());
        });

        $binder->bind('creditCard.email', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Email());
        });

        $binder->bind('creditCard.phone', function(Property $property){
            $property
                ->addRule(new Blank());
        });

        (new LocationDefiner($this->stateService))
            ->setHolder('creditCard', false)
            ->setSingleAddress(true)
            ->define($binder);

        $binder->bind('amount', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Greater(0));
        });
    }
}
