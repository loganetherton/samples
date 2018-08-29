<?php
namespace ValuePad\Core\User\Interfaces;
use ValuePad\Core\Location\Entities\State;

interface LocationAwareInterface
{
    /**
     * @param string $address
     */
    public function setAddress1($address);

    /**
     * @return string
     */
    public function getAddress1();

    /**
     * @param string $address
     */
    public function setAddress2($address);

    /**
     * @return string
     */
    public function getAddress2();

    /**
     * @param string $city
     */
    public function setCity($city);

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param string $zip
     */
    public function setZip($zip);

    /**
     * @return string
     */
    public function getZip();

    /**
     * @param State $state
     */
    public function setState(State $state);

    /**
     * @return State
     */
    public function getState();
}
