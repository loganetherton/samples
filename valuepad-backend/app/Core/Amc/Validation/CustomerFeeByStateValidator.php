<?php
namespace ValuePad\Core\Amc\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Amc\Entities\CustomerFeeByState;
use ValuePad\Core\Amc\Services\CustomerFeeService;
use ValuePad\Core\Amc\Validation\Rules\CustomerFeeByStateStateUnique;
use ValuePad\Core\Assignee\Entities\CustomerFee;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Rules\StateExists;
use ValuePad\Core\Support\Service\ContainerInterface;

class CustomerFeeByStateValidator extends AbstractThrowableValidator
{
    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @var CustomerFeeService
     */
    private $feeService;

    /**
     * @var CustomerFeeByState
     */
    private $currentFeeByState;

    /**
     * @var CustomerFee
     */
    private $currentFee;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->feeService = $container->get(CustomerFeeService::class);
        $this->stateService = $container->get(StateService::class);
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('state', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new StateExists($this->stateService))
                ->addRule(new CustomerFeeByStateStateUnique($this->feeService, $this->currentFee, $this->currentFeeByState));
        });

        $binder->bind('amount', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Greater(0));
        });
    }

    /**
     * @param CustomerFeeByState $feeByState
     * @return $this
     */
    public function setCurrentFeeByState(CustomerFeeByState $feeByState)
    {
        $this->currentFeeByState = $feeByState;
        return $this;
    }

    /**
     * @param CustomerFee $fee
     * @return $this
     */
    public function setCurrentFee(CustomerFee $fee)
    {
        $this->currentFee = $fee;
        return $this;
    }
}
