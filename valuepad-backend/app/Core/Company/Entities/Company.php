<?php
namespace ValuePad\Core\Company\Entities;

use DateTime;
use ValuePad\Core\Appraiser\Entities\Ach;
use ValuePad\Core\Appraiser\Entities\Eo;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Support\DocumentUsageManagementTrait;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\User\Entities\User;

class Company
{
    use DocumentUsageManagementTrait;

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
     * @var Document
     */
    private $w9;
    public function getW9() { return $this->w9; }

    /**
     * @param Document $document
     */
    public function setW9(Document $document)
    {
        $this->handleUsageOfOneDocument($this->getW9(), $document);

        $this->w9 = $document;
    }

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
     * @var State
     */
    private $state;
    public function setState(State $state ) { $this->state = $state; }
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
     * @var Eo
     */
    private $eo;
    public function setEo(Eo $eo) { $this->eo = $eo; }
    public function getEo() { return $this->eo; }

    /**
     * @var Ach
     */
    private $ach;
    public function setAch(Ach $ach) { $this->ach = $ach; }
    public function getAch() { return $this->ach; }

    /**
     * We're remembering the user who created the appraisal company because
     * there's a possibility in the future that we might want to allow
     * admins who haven't created any appraisal companies to be able to do so.
     *
     * @var User
     */
    private $creator;
    public function setCreator(User $creator) { $this->creator = $creator; }
    public function getCreator() { return $this->creator; }

    /**
     * @var DateTime
     */
    private $createdAt;
    public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
    public function getCreatedAt() { return $this->createdAt; }

    /**
     * @var DateTime
     */
    private $updatedAt;
    public function setUpdatedAt(DateTime $datetime) { $this->updatedAt = $datetime; }
    public function getUpdatedAt() { return $this->updatedAt; }

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }
}
