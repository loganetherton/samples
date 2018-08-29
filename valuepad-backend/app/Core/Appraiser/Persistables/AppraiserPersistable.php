<?php
namespace ValuePad\Core\Appraiser\Persistables;

use ValuePad\Core\Appraiser\Enums\BusinessTypes;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Document\Persistables\Identifier;
use ValuePad\Core\Document\Persistables\Identifiers;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Core\User\Persistables\UserPersistable;
use ValuePad\Core\Shared\Persistables\AvailabilityPersistable;
use DateTime;

/**
 *
 *
 */
class AppraiserPersistable extends UserPersistable
{
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
	private $email;
	public function setEmail($email) { $this->email = $email; }
	public function getEmail() { return $this->email; }

	/**
	 * @var string
	 */
	private $companyName;
	public function setCompanyName($name) { $this->companyName = $name; }
	public function getCompanyName() { return $this->companyName; }

	/**
	 * @var BusinessTypes
	 */
	private $businessTypes;
	public function getBusinessTypes() { return $this->businessTypes; }
	public function setBusinessTypes(BusinessTypes $businessTypes) { $this->businessTypes = $businessTypes; }

	/**
	 * @var CompanyType
	 */
	private $companyType;
	public function getCompanyType() { return $this->companyType; }
	public function setCompanyType(CompanyType $companyType) { $this->companyType = $companyType; }

	/**
	 * @var string
	 */
	private $otherCompanyType;
	public function getOtherCompanyType() { return $this->otherCompanyType; }
	public function setOtherCompanyType($type) { $this->otherCompanyType = $type; }

	/**
	 * @var string
	 */
	private $taxIdentificationNumber;
	public function getTaxIdentificationNumber() { return $this->taxIdentificationNumber; }
	public function setTaxIdentificationNumber($taxIdentificationNumber) { $this->taxIdentificationNumber = $taxIdentificationNumber; }

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
	private $state;
	public function setState($state) { $this->state = $state; }
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
	 * @var string
	 */
	private $assignmentAddress1;
	public function setAssignmentAddress1($address) { $this->assignmentAddress1 = $address; }
	public function getAssignmentAddress1() { return $this->assignmentAddress1; }

	/**
	 * @var string
	 */
	private $assignmentAddress2;
	public function setAssignmentAddress2($address) { $this->assignmentAddress2 = $address; }
	public function getAssignmentAddress2() { return $this->assignmentAddress2; }

	/**
	 * @var string
	 */
	private $assignmentCity;
	public function setAssignmentCity($city) { $this->assignmentCity = $city; }
	public function getAssignmentCity() { return $this->assignmentCity; }

	/**
	 * @var string
	 */
	private $assignmentZip;
	public function setAssignmentZip($zip) { $this->assignmentZip = $zip; }
	public function getAssignmentZip() { return $this->assignmentZip; }

	/**
	 * @var string
	 */
	private $assignmentState;
	public function setAssignmentState($state) { $this->assignmentState = $state; }
	public function getAssignmentState() { return $this->assignmentState; }

	/**
	 * @var string
	 */
	private $phone;
	public function getPhone() { return $this->phone; }
	public function setPhone($phone) { $this->phone = $phone; }

	/**
	 * @var string
	 */
	private $cell;
	public function setCell($cell) { $this->cell = $cell; }
	public function getCell() { return $this->cell; }

	/**
	 * @var string
	 */
	private $fax;
	public function setFax($fax) { $this->fax = $fax; }
	public function getFax() { return $this->fax; }

	/**
	 * @var string
	 */
	private $signature;
	public function getSignature() { return $this->signature; }
	public function setSignature($signature) { $this->signature = $signature; }

	/**
	 * @var DateTime
	 */
	private $signedAt;
	public function getSignedAt() { return $this->signedAt; }
	public function setSignedAt(DateTime $signedAt) { $this->signedAt = $signedAt; }

	/**
	 * @var bool
	 */
	private $showInitialDisplay;
	public function setShowInitialDisplay($flag) { $this->showInitialDisplay = $flag; }
	public function getShowInitialDisplay() { return $this->showInitialDisplay; }

    /**
     * @var string[]
     */
    private $languages;
	public function setLanguages(array $codes) { $this->languages = $codes; }
	public function getLanguages() { return $this->languages; }

    /**
     * @var Identifiers|Identifier[]
     */
    private $sampleReports;
	public function setSampleReports(Identifiers $documents) { $this->sampleReports = $documents; }
	public function getSampleReports() { return $this->sampleReports; }

	/**
	 * @var EoExPersistable
	 */
	private $eo;
	public function setEo(EoExPersistable $persistable) { $this->eo = $persistable; }
	public function getEo() { return $this->eo; }

	/**
	 * @var QualificationsPersistable
	 */
	private $qualifications;
	public function setQualifications(QualificationsPersistable $persistable) { $this->qualifications = $persistable; }
	public function getQualifications() { return $this->qualifications; }

	/**
	 * @var Identifier
	 */
	private $w9;
	public function setW9(Identifier $identifier) { $this->w9 = $identifier; }
	public function getW9() { return $this->w9; }

	/**
	 * @var AvailabilityPersistable
	 */
	private $availability;
	public function setAvailability(AvailabilityPersistable $availability) { $this->availability = $availability; }
	public function getAvailability() { return $this->availability; }

	/**
	 * @var Status
	 */
	private $status;
	public function setStatus(Status $status) { $this->status = $status; }
	public function getStatus() { return $this->status; }

}
