<?php
namespace ValuePad\Core\Payment\Objects;
use ValuePad\Core\Location\Entities\State;

abstract class AbstractPaymentMethod
{
    /**
     * @var string
     */
    private $address;
    public function setAddress($address) { $this->address = $address; }
    public function getAddress() { return $this->address; }

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
    public function setState(State $state) { $this->state = $state; }
    public function getState()  { return $this->state; }
}
