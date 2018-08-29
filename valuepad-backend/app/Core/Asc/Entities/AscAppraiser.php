<?php
namespace ValuePad\Core\Asc\Entities;

use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Asc\Enums\Certifications;
use ValuePad\Core\Location\Entities\State;
use DateTime;

class AscAppraiser
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
    private $firstName;
    public function setFirstName($firstName) { $this->firstName = $firstName; }
    public function getFirstName() { return $this->firstName;}

    /**
     * @var string
     */
    private $lastName;
    public function setLastName($lastName) { $this->lastName = $lastName; }
    public function getLastName() { return $this->lastName; }

    /**
     * @var string
     */
    private $phone;
    public function getPhone() { return $this->phone; }
    public function setPhone($phone) { $this->phone = $phone; }

    /**
     * @var string
     */
    private $companyName;
    public function setCompanyName($name) { $this->companyName = $name; }
    public function getCompanyName() { return $this->companyName; }

    /**
     * @var string
     */
    private $address;
    public function setAddress($address) { $this->address = $address; }
    public function getAddress() { return $this->address; }

    /**
     * @var State
     */
    private $state;
    public function setState(State $state = null) { $this->state = $state; }
    public function getState() { return $this->state; }

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
     * @var Certifications
     */
    private $certifications;
    public function setCertifications(Certifications $certifications) { $this->certifications = $certifications; }
    public function getCertifications() { return $this->certifications; }

    /**
     * @var string
     */
    private $licenseNumber;
    public function setLicenseNumber($number) { $this->licenseNumber = $number; }
    public function getLicenseNumber() { return $this->licenseNumber; }

    /**
     * @var DateTime
     */
    private $licenseExpiresAt;
    public function setLicenseExpiresAt(DateTime $datetime) { $this->licenseExpiresAt = $datetime; }
    public function getLicenseExpiresAt() { return $this->licenseExpiresAt; }


    /**
     * @var Appraiser
     */
    private $appraiser;
    public function setAppraiser(Appraiser $appraiser = null) { $this->appraiser = $appraiser; }
    public function getAppraiser() { return $this->appraiser; }

	/**
     * @var State
     */
    private $licenseState;
    public function setLicenseState(State $state) { $this->licenseState = $state; }
    public function getLicenseState() { return $this->licenseState; }
}
