<?php
namespace ValuePad\Core\Company\Persistables;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Appraiser\Persistables\AchPersistable;
use ValuePad\Core\Appraiser\Persistables\EoPersistable;
use ValuePad\Core\Document\Persistables\Identifier;

class CompanyPersistable
{
    /**
     * @var string
     */
    private $name;
    public function setName($name) { $this->name = $name; }
    public function getName() { return $this->name; }

    /**
     * @var CompanyType
     */
    private $type;
    public function getType() { return $this->type; }
    public function setType(CompanyType $type) { $this->type = $type; }

    /**
     * @var string
     */
    private $otherType;
    public function setOtherType($otherType) { $this->otherType = $otherType; }
    public function getOtherType() { return $this->otherType; }

    /**
     * @var string
     */
    private $taxId;
    public function getTaxId() { return $this->taxId; }
    public function setTaxId($taxId) { $this->taxId = $taxId; }

    /**
     * @var Identifier
     */
    private $w9;
    public function setW9(Identifier $identifier) { $this->w9 = $identifier; }
    public function getW9() { return $this->w9; }

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
    private $assignmentZip;
    public function setAssignmentZip($assignmentZip) { $this->assignmentZip = $assignmentZip; }
    public function getAssignmentZip() { return $this->assignmentZip; }

    /**
     * @var string
     */
    private $state;
    public function setState($state ) { $this->state = $state; }
    public function getState() { return $this->state; }


    /**
     * @var string
     */
    private $firstName;
    public function getFirstName() { return $this->firstName; }
    public function setFirstName($firstName) { $this->firstName = $firstName; }

    /**
     * @var string
     */
    private $lastName;
    public function getLastName() { return $this->lastName; }
    public function setLastName($lastName) { $this->lastName = $lastName; }

    /**
     * @var string
     */
    private $email;
    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }

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
     * @var EoPersistable
     */
    private $eo;
    public function setEo(EoPersistable $eo) { $this->eo = $eo; }
    public function getEo() { return $this->eo; }

    /**
     * @var AchPersistable
     */
    private $ach;
    public function setAch(AchPersistable $ach) { $this->ach = $ach; }
    public function getAch() { return $this->ach; }
}
