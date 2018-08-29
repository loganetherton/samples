<?php
namespace ValuePad\Core\Amc\Entities;
use Doctrine\Common\Collections\ArrayCollection;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\User\Interfaces\BusinessInterface;
use ValuePad\Core\User\Interfaces\EmailHolderInterface;
use Traversable;
use ValuePad\Core\User\Interfaces\FaxHolderInterface;
use ValuePad\Core\User\Interfaces\LocationAwareInterface;
use ValuePad\Core\User\Interfaces\PhoneHolderInterface;

class Amc extends User implements EmailHolderInterface,
    BusinessInterface,
    FaxHolderInterface,
    PhoneHolderInterface,
    LocationAwareInterface
{
    /**
     * @var string
     */
    private $companyName;
    public function setCompanyName($name) { $this->companyName = $name;}
    public function getCompanyName() { return $this->companyName; }


    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }

    /**
     * @var string
     */
    private $address1;
    public function setAddress1($address) { $this->address1 = $address; }
    public function getAddress1() { return $this->address1; }

    /**
     * @var string
     */
    private $address2;
    public function setAddress2($address) { $this->address2 = $address; }
    public function getAddress2() { return $this->address2; }

    /**
     * @var string
     */
    private $city;
    public function setCity($city) { $this->city = $city; }
    public function getCity() { return $this->city; }

    /**
     * @var string
     */
    private $zip;
    public function setZip($zip) { $this->zip = $zip; }
    public function getZip() { return $this->zip; }

    /**
     * @var State
     */
    private $state;
    public function setState(State $state ) { $this->state = $state; }
    public function getState() { return $this->state; }

    /**
     * @var string
     */
    private $phone;
    public function getPhone() { return $this->phone; }
    public function setPhone($phone) { $this->phone = $phone; }

    /**
     * @var string
     */
    private $fax;
    public function setFax($fax) { $this->fax = $fax; }
    public function getFax() { return $this->fax; }

    /**
     * @var string
     */
    private $lenders;
    public function setLenders($lenders) { $this->lenders = $lenders; }
    public function getLenders() { return $this->lenders; }

    /**
     * @var Customer[]|Traversable
     */
    private $customers;
    public function getCustomers() { return $this->customers; }
    public function addCustomer(Customer $customer) { $this->customers->add($customer); }

    /**
     * @return string
     */
    public function getDisplayName() { return $this->getCompanyName(); }

    /**
     * @var string
     */
    private $secret1;
    public function setSecret1($secret) { $this->secret1 = $secret; }
    public function getSecret1() { return $this->secret1; }

    /**
     * @var string
     */
    private $secret2;
    public function setSecret2($secret) { $this->secret2 = $secret; }
    public function getSecret2() { return $this->secret2; }

    /**
     * @var Settings
     */
    private $settings;

    /**
     * @return Settings
     */
    public function getSettings() { return $this->settings->first()?:null; }


    /**
     * @param Settings $settings
     */
    public function setSettings(Settings $settings)
    {
        $this->settings->clear();
        $this->settings->add($settings);
    }

    public function __construct()
    {
        parent::__construct();
        $this->setStatus(new Status(Status::PENDING));
        $this->customers = new ArrayCollection();
        $this->settings = new ArrayCollection();
    }
}
