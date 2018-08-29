<?php
namespace ValuePad\Core\Customer\Entities;
use ValuePad\Core\Location\Entities\State;

class Client
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var string
     */
    private $name;
    public function setName($name) { $this->name = $name; }
    public function getName() { return $this->name; }

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
    public function setState(State $state = null) { $this->state = $state; }
    public function getState() { return $this->state; }


    /**
     * @var Customer
     */
    private $customer;
    public function setCustomer(Customer $customer) { $this->customer = $customer; }
    public function getCustomer() { return $this->customer; }
}
