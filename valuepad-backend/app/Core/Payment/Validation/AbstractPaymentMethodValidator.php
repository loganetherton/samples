<?php
namespace ValuePad\Core\Payment\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Definer\LocationDefiner;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\User\Interfaces\LocationAwareInterface;

abstract class AbstractPaymentMethodValidator extends AbstractThrowableValidator
{
    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @var User
     */
    private $owner;

    /**
     * @param StateService $stateService
     * @param User $owner
     */
    public function __construct(StateService $stateService, User $owner)
    {
        $this->stateService = $stateService;
        $this->owner = $owner;
    }

    /**
     * @param Binder $binder
     */
    protected function define(Binder $binder)
    {
        (new LocationDefiner($this->stateService))
            ->setSingleAddress(true)
            ->setObligate(!($this->owner instanceof LocationAwareInterface))
            ->define($binder);
    }
}
