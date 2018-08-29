<?php
namespace ValuePad\Core\Company\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Shared\Entities\Availability;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\User\Interfaces\EmailHolderInterface;
use ValuePad\Core\User\Interfaces\IndividualInterface;
use ValuePad\Core\User\Interfaces\PhoneHolderInterface;

class Manager extends User implements EmailHolderInterface,
    PhoneHolderInterface,
    IndividualInterface
{
    /**
     * @var string
     */
    private $firstName;
    public function setFirstName($firstName) { $this->firstName = $firstName; }
    public function getFirstName() { return $this->firstName; }

    /**
     * @var string
     */
    private $lastName;
    public function setLastName($lastName) { $this->lastName = $lastName; }
    public function getLastName() { return $this->lastName; }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    /**
     * @var string
     */
    private $phone;
    public function setPhone($phone) { $this->phone = $phone; }
    public function getPhone() { return $this->phone; }

    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }

    /**
     * @var Staff
     */
    private $staff;
    public function setStaff(Staff $staff) { $this->staff = $staff; }
    public function getStaff() { return $this->staff; }

    /**
     * @var Availability
     */
    private $availability;
    public function setAvailability(Availability $availability) { $this->availability = $availability; }
    public function getAvailability() { return $this->availability;}

    /**
     * @var Customer[]
     */
    private $customers;
    public function getCustomers() { return $this->customers; }

    /**
     * @param Customer $customer
     */
    public function addCustomer(Customer $customer)
    {
        $this->customers->add($customer);
    }

    /**
     * @return string
     */
    public function getAddress1() { return $this->getStaff()->getCompany()->getAddress1(); }

    /**
     * @return string
     */
    public function getAddress2() { return $this->getStaff()->getCompany()->getAddress2(); }

    /**
     * @return string
     */
    public function getCity() { return $this->getStaff()->getCompany()->getCity(); }

    /**
     * @return string
     */
    public function getZip() { return $this->getStaff()->getCompany()->getZip(); }

    /**
     * @return State
     */
    public function getState() { return $this->getStaff()->getCompany()->getState(); }

    /**
     * @return string
     */
    public function getAssignmentAddress1() { return $this->getStaff()->getBranch()->getAddress1(); }

    /**
     * @return string
     */
    public function getAssignmentAddress2() { return $this->getStaff()->getBranch()->getAddress2(); }

    /**
     * @return string
     */
    public function getAssignmentCity() { return $this->getStaff()->getBranch()->getCity(); }

    /**
     * @return string
     */
    public function getAssignmentZip() { return $this->getStaff()->getBranch()->getZip(); }

    /**
     * @return State
     */
    public function getAssignmentState() { return $this->getStaff()->getBranch()->getState(); }

    public function __construct()
    {
        parent::__construct();
        $this->customers = new ArrayCollection();
    }
}
