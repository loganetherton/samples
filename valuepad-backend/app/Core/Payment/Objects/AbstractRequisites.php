<?php
namespace ValuePad\Core\Payment\Objects;

abstract class AbstractRequisites
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
     * @var string
     */
    private $state;
    public function setState($state) { $this->state = $state; }
    public function getState()  { return $this->state; }
}
