<?php
namespace ValuePad\Core\Amc\Persistables;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Core\User\Persistables\UserPersistable;

class AmcPersistable extends UserPersistable
{
    /**
     * @var Status
     */
    private $status;
    public function setStatus(Status $status) { $this->status = $status; }
    public function getStatus() { return $this->status; }

    /**
     * @var string
     */
    private $companyName;
    public function setCompanyName($name) { $this->companyName = $name;}
    public function getCompanyName() { return $this->companyName; }

    /**
     * @var string
     */
    private $email;
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
     * @var string
     */
    private $state;
    public function setState($state) { $this->state = $state; }
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
}
