<?php
namespace ValuePad\Core\Shared\Entities;

use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\User\Entities\User;

class AvailabilityPerCustomer extends AbstractAvailability
{
    /**
     * @var Customer
     */
    private $customer;
    public function setCustomer(Customer $customer) { $this->customer = $customer; }
    public function getCustomer() { return $this->customer; }

    /**
     * @var Appraiser|Manager
     */
    private $user;
    public function setUser(User $user) { $this->user = $user; }
    public function getUser() { return $this->user; }
}
