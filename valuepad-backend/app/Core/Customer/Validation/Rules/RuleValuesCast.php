<?php
namespace ValuePad\Core\Customer\Validation\Rules;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\BooleanCast;
use Ascope\Libraries\Validation\Rules\Composite;
use Ascope\Libraries\Validation\Rules\StringCast;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Customer\Enums\Rule;
use ValuePad\Core\Customer\Validation\Inflators\ClientAddressInflator;
use ValuePad\Core\Customer\Validation\Inflators\ClientCityInflator;
use ValuePad\Core\Customer\Validation\Inflators\ClientZipInflator;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Inflators\StateInflator;
use ValuePad\Core\Shared\Validation\Rules\NotNullable;

class RuleValuesCast extends Composite
{
    /**
     * @param StateService $stateService
     */
    public function __construct(StateService $stateService)
    {
        parent::__construct(function(Binder $binder) use ($stateService){

            $binder->bind(Rule::REQUIRE_ENV, function(Property $property){
                $property->addRule(new NotNullable());
            })->when(function(SourceHandlerInterface $source){
                return $source->hasProperty(Rule::REQUIRE_ENV);
            });

            $binder->bind(Rule::REQUIRE_ENV, function(Property $property){
                $property->addRule(new BooleanCast());
            });

            $binder->bind(Rule::CLIENT_ADDRESS_1, function(Property $property){
                $property->addRule(new StringCast());
            });

            $binder->bind(Rule::CLIENT_ADDRESS_1, new ClientAddressInflator());

            $binder->bind(Rule::CLIENT_ADDRESS_2, function(Property $property){
                $property->addRule(new StringCast());
            });

            $binder->bind(Rule::CLIENT_ADDRESS_2, new ClientAddressInflator());

            $binder->bind(Rule::CLIENT_CITY, function(Property $property){
                $property->addRule(new StringCast());
            });

            $binder->bind(Rule::CLIENT_CITY, new ClientCityInflator());

            $binder->bind(Rule::CLIENT_ZIP, function(Property $property){
                $property->addRule(new StringCast());
            });

            $binder->bind(Rule::CLIENT_ZIP, new ClientZipInflator());

            $binder->bind(Rule::CLIENT_STATE, function(Property $property){
                $property->addRule(new StringCast());
            });

            $binder->bind(Rule::CLIENT_STATE, (new StateInflator($stateService))->setRequired(false));

            // CLIENT DISPLAYED ON REPORT

            $binder->bind(Rule::CLIENT_DISPLAYED_ON_REPORT_ADDRESS_1, function(Property $property){
                $property->addRule(new StringCast());
            });

            $binder->bind(Rule::CLIENT_DISPLAYED_ON_REPORT_ADDRESS_1, new ClientAddressInflator());

            $binder->bind(Rule::CLIENT_DISPLAYED_ON_REPORT_ADDRESS_2, function(Property $property){
                $property->addRule(new StringCast());
            });

            $binder->bind(Rule::CLIENT_DISPLAYED_ON_REPORT_ADDRESS_2, new ClientAddressInflator());

            $binder->bind(Rule::CLIENT_DISPLAYED_ON_REPORT_CITY, function(Property $property){
                $property->addRule(new StringCast());
            });

            $binder->bind(Rule::CLIENT_DISPLAYED_ON_REPORT_CITY, new ClientCityInflator());

            $binder->bind(Rule::CLIENT_DISPLAYED_ON_REPORT_ZIP, function(Property $property){
                $property->addRule(new StringCast());
            });

            $binder->bind(Rule::CLIENT_DISPLAYED_ON_REPORT_ZIP, new ClientZipInflator());

            $binder->bind(Rule::CLIENT_DISPLAYED_ON_REPORT_STATE, function(Property $property){
                $property->addRule(new StringCast());
            });

            $binder->bind(Rule::CLIENT_DISPLAYED_ON_REPORT_STATE, (new StateInflator($stateService))->setRequired(false));

            $binder->bind(Rule::DISPLAY_FDIC, function(Property $property){
                $property->addRule(new NotNullable());
            })->when(function(SourceHandlerInterface $source){
                return $source->hasProperty(Rule::DISPLAY_FDIC);
            });

            $binder->bind(Rule::DISPLAY_FDIC, function(Property $property){
                $property->addRule(new BooleanCast());
            });
        });
    }
}
