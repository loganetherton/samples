<?php
namespace ValuePad\Core\Payment\Validation;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Numeric;
use Ascope\Libraries\Validation\Rules\Obligate;

class BankAccountValidator extends AbstractPaymentMethodValidator
{
       /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        parent::define($binder);

        $binder->bind('accountType', function(Property $property){
            $property
                ->addRule(new Obligate());
        });

        $binder->bind('routingNumber', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Length(9))
                ->addRule(new Numeric());
        });

        $binder->bind('accountNumber', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Length(5, 17))
                ->addRule(new Numeric());
        });

        $binder->bind('nameOnAccount', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank())
                ->addRule(new Length(0, 22));
        });

        $binder->bind('bankName', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank())
                ->addRule(new Length(0, 50));
        });
    }
}
