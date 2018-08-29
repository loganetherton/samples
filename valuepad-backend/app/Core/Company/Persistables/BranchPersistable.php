<?php
namespace ValuePad\Core\Company\Persistables;

use ValuePad\Core\Appraiser\Persistables\EoPersistable;

class BranchPersistable
{
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
     * @var string
     */
    private $state;
    public function setState($state) { $this->state = $state; }
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
     * @var EoPersistable
     */
    private $eo;
    public function setEo(EoPersistable $eo) { $this->eo = $eo; }
    public function getEo() { return $this->eo; }
}
