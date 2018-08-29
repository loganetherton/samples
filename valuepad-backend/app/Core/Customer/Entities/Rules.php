<?php
namespace ValuePad\Core\Customer\Entities;
use ValuePad\Core\Location\Entities\State;

class Rules
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var array
     */
    private $available = [];
    public function getAvailable() { return $this->available; }
    public function addAvailable($available) { $this->available[] = $available; }

    /**
     * @var bool
     */
    private $requireEnv;
    public function setRequireEnv($flag) { $this->requireEnv = $flag; }
    public function getRequireEnv() { return $this->requireEnv; }

    /**
     * @var string
     */
    private $clientAddress1;
    public function setClientAddress1($address) { $this->clientAddress1 = $address; }
    public function getClientAddress1() { return $this->clientAddress1; }

    /**
     * @var string
     */
    private $clientAddress2;
    public function setClientAddress2($address) { $this->clientAddress2 = $address; }
    public function getClientAddress2() { return $this->clientAddress2; }

    /**
     * @var string
     */
    private $clientCity;
    public function setClientCity($city) { $this->clientCity = $city; }
    public function getClientCity() { return $this->clientCity; }

    /**
     * @var State
     */
    private $clientState;
    public function setClientState(State $state = null) { $this->clientState = $state; }
    public function getClientState() { return $this->clientState; }

    /**
     * @var string
     */
    private $clientZip;
    public function setClientZip($zip) { $this->clientZip = $zip; }
    public function getClientZip() { return $this->clientZip; }

    /**
     * @var string
     */
    private $clientDisplayedOnReportAddress1;
    public function setClientDisplayedOnReportAddress1($address) { $this->clientDisplayedOnReportAddress1 = $address; }
    public function getClientDisplayedOnReportAddress1() { return $this->clientDisplayedOnReportAddress1; }

    /**
     * @var string
     */
    private $clientDisplayedOnReportAddress2;
    public function setClientDisplayedOnReportAddress2($address) { $this->clientDisplayedOnReportAddress2 = $address; }
    public function getClientDisplayedOnReportAddress2() { return $this->clientDisplayedOnReportAddress2; }

    /**
     * @var string
     */
    private $clientDisplayedOnReportCity;
    public function setClientDisplayedOnReportCity($city) { $this->clientDisplayedOnReportCity = $city; }
    public function getClientDisplayedOnReportCity() { return $this->clientDisplayedOnReportCity; }

    /**
     * @var State
     */
    private $clientDisplayedOnReportState;
    public function setClientDisplayedOnReportState(State $state = null) { $this->clientDisplayedOnReportState = $state; }
    public function getClientDisplayedOnReportState() { return $this->clientDisplayedOnReportState; }

    /**
     * @var string
     */
    private $clientDisplayedOnReportZip;
    public function setClientDisplayedOnReportZip($zip) { $this->clientDisplayedOnReportZip = $zip; }
    public function getClientDisplayedOnReportZip() { return $this->clientDisplayedOnReportZip; }

    /**
     * @var bool
     */
    private $displayFdic;
    public function setDisplayFdic($flag) { $this->displayFdic = $flag; }
    public function getDisplayFdic() { return $this->displayFdic; }

    public function reset()
    {
        $this->available = [];
        $this->requireEnv = null;

        $this->clientAddress1 = null;
        $this->clientAddress2 = null;
        $this->clientCity = null;
        $this->clientState = null;
        $this->clientZip = null;

        $this->clientDisplayedOnReportAddress1 = null;
        $this->clientDisplayedOnReportAddress2 = null;
        $this->clientDisplayedOnReportCity = null;
        $this->clientDisplayedOnReportState = null;
        $this->clientDisplayedOnReportZip = null;

        $this->displayFdic = null;
    }
}
