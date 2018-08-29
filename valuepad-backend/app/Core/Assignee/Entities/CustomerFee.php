<?php
namespace ValuePad\Core\Assignee\Entities;

use InvalidArgumentException;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\User\Entities\User;

class CustomerFee
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var float
     */
    private $amount;
    public function setAmount($amount){ $this->amount = $amount; }
    public function getAmount() { return $this->amount; }

    /**
     * @var JobType
     */
    private $jobType;
    public function setJobType(JobType $jobType) { $this->jobType = $jobType; }
    public function getJobType() { return $this->jobType; }

    /**
     * @var Customer
     */
    private $customer;
    public function setCustomer(Customer $customer) { $this->customer = $customer; }
    public function getCustomer() { return $this->customer; }

    /**
     * @var User|Appraiser|Amc
     */
    private $assignee;
    public function getAssignee() { return $this->assignee; }

    public function setAssignee(User $assignee)
    {
        if (!$assignee instanceof Appraiser && !$assignee instanceof Amc) {
            throw new InvalidArgumentException('Invalid assignee type');
        }

        $this->assignee = $assignee;
    }
}
