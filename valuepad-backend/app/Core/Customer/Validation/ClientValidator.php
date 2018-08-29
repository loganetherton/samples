<?php
namespace ValuePad\Core\Customer\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Customer\Validation\Inflators\ClientAddressInflator;
use ValuePad\Core\Customer\Validation\Inflators\ClientCityInflator;
use ValuePad\Core\Customer\Validation\Inflators\ClientZipInflator;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Inflators\StateInflator;

class ClientValidator extends AbstractThrowableValidator
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
        $binder->bind('name', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank())
                ->addRule(new Length(1, 255));
        });

        $binder->bind('address1', new ClientAddressInflator());
        $binder->bind('address2', new ClientAddressInflator());
        $binder->bind('city', new ClientCityInflator());
        $binder->bind('zip', new ClientZipInflator());
        $binder->bind('state', (new StateInflator($this->stateService))->setRequired(false));
    }
}
