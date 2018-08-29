<?php
namespace ValuePad\Core\Amc\Notifications;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Enums\Scope;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Location\Entities\State;

class ChangeCustomerFeesNotification extends AbstractNotification
{
    /**
     * @var Customer $customer
     */
    private $customer;

    /**
     * @var Scope
     */
    private $scope;

    /**
     * @var State
     */
    private $state;

    /**
     * @var JobType
     */
    private $jobType;

    /**
     * @param Amc $amc
     * @param Customer $customer
     * @param Scope $scope
     */
    public function __construct(Amc $amc, Customer $customer, Scope $scope)
    {
        parent::__construct($amc);

        $this->customer = $customer;
        $this->scope = $scope;
    }

    /**
     * @return Scope
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param State $state
     */
    public function setState(State $state)
    {
        $this->state = $state;
    }

    /**
     * @return State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param JobType $jobType
     */
    public function setJobType(JobType $jobType)
    {
        $this->jobType = $jobType;
    }

    /**
     * @return JobType
     */
    public function getJobType()
    {
        return $this->jobType;
    }
}
