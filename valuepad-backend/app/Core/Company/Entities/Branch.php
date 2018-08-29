<?php
namespace ValuePad\Core\Company\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use ValuePad\Core\Appraiser\Entities\Eo;
use ValuePad\Core\Location\Entities\State;

class Branch
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var bool
     */
    private $isDefault = false;
    public function setDefault($isDefault) { $this->isDefault = $isDefault; }
    public function isDefault() { return $this->isDefault; }

    /**
     * @var string
     */
    private $name;
    public function setName($name) { $this->name = $name; }
    public function getName() { return $this->name; }

    /**
     * @var string
     */
    private $taxId;
    public function setTaxId($taxId) { $this->taxId = $taxId; }
    public function getTaxId() { return $this->taxId; }

    /**
     * @var string
     */
    private $address1;
    public function setAddress1($address1) { $this->address1 = $address1; }
    public function getAddress1() { return $this->address1; }

    /**
     * @var string
     */
    private $address2;
    public function setAddress2($address2) { $this->address2 = $address2; }
    public function getAddress2() { return $this->address2; }

    /**
     * @var string
     */
    private $city;
    public function setCity($city) { $this->city = $city; }
    public function getCity() { return $this->city; }

    /**
     * @var State
     */
    private $state;
    public function setState(State $state) { $this->state = $state; }
    public function getState() { return $this->state; }

    /**
     * @var string
     */
    private $zip;
    public function setZip($zip) { $this->zip = $zip; }
    public function getZip() { return $this->zip; }

    /**
     * @var string
     */
    private $assignmentZip;
    public function setAssignmentZip($assignmentZip) { $this->assignmentZip = $assignmentZip; }
    public function getAssignmentZip() { return $this->assignmentZip; }

    /**
     * @var Eo
     */
    private $eo;
    public function setEo(Eo $eo) { $this->eo = $eo; }
    public function getEo() { return $this->eo; }

    /**
     * @var Company
     */
    private $company;
    public function setCompany(Company $company) { $this->company = $company; }
    public function getCompany() { return $this->company; }

    /**
     * @var Staff[]|ArrayCollection
     */
    private $staff;
    public function addStaff(Staff $staff) { $this->staff->add($staff); }
    public function getStaff() { return $this->staff; }

    public function __construct()
    {
        $this->staff = new ArrayCollection();
    }
}
