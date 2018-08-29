<?php
namespace ValuePad\Core\Amc\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Amc\Entities\Fee;
use ValuePad\Core\Amc\Entities\FeeByState;
use ValuePad\Core\Amc\Services\FeeService;
use ValuePad\Core\Amc\Validation\Rules\FeeByStateStateUnique;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Rules\StateExists;
use ValuePad\Core\Support\Service\ContainerInterface;

class FeeByStateValidator extends AbstractThrowableValidator
{
    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @var FeeService
     */
    private $feeService;

    /**
     * @var FeeByState
     */
    private $currentFeeByState;

    /**
     * @var Fee
     */
    private $currentFee;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->feeService = $container->get(FeeService::class);
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
                ->addRule(new FeeByStateStateUnique($this->feeService, $this->currentFee, $this->currentFeeByState));
        });

        $binder->bind('amount', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Greater(0));
        });
    }

    /**
     * @param FeeByState $feeByState
     * @return $this
     */
    public function setCurrentFeeByState(FeeByState $feeByState)
    {
        $this->currentFeeByState = $feeByState;
        return $this;
    }

    /**
     * @param Fee $fee
     * @return $this
     */
    public function setCurrentFee(Fee $fee)
    {
        $this->currentFee = $fee;
        return $this;
    }
}
