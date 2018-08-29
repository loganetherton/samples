<?php
namespace ValuePad\Core\Appraiser\Entities;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Traversable;
use ValuePad\Core\Appraiser\Enums\BusinessTypes;
use ValuePad\Core\Appraiser\Enums\CompanyType;
use ValuePad\Core\Asc\Entities\AscAppraiser;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Support\DocumentUsageManagementTrait;
use ValuePad\Core\Language\Entities\Language;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\User\Interfaces\BusinessInterface;
use ValuePad\Core\User\Interfaces\EmailHolderInterface;
use ValuePad\Core\User\Interfaces\FaxHolderInterface;
use ValuePad\Core\User\Interfaces\IndividualInterface;
use ValuePad\Core\User\Interfaces\LocationAwareInterface;
use ValuePad\Core\User\Interfaces\PhoneHolderInterface;
use ValuePad\Core\Shared\Entities\Availability;

class Appraiser extends User implements EmailHolderInterface,
    BusinessInterface,
    FaxHolderInterface,
    PhoneHolderInterface,
    IndividualInterface,
    LocationAwareInterface
{
    use DocumentUsageManagementTrait;

	/**
	 * @var string
	 */
	private $firstName;
    public function getFirstName() { return $this->firstName;}

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        $this->refreshFullName();
    }

	/**
	 * @var string
	 */
	private $lastName;
	public function getLastName() { return $this->lastName; }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        $this->refreshFullName();
    }

    /**
     * @var string
     */
	private $fullName;

    /**
     * @return string
     */
    private function refreshFullName()
    {
        $this->fullName = trim($this->firstName.' '.$this->lastName);
    }

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
	 * @var State
	 */
	private $state;
	public function setState(State $state) { $this->state = $state; }
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
	 * @var Language[]
	 */
	private $languages;
	public function getLanguages() { return $this->languages; }

	/**
	 * @param Language[] $languages
	 */
	public function setLanguages(array $languages)
	{
		$this->languages->clear();

		foreach ($languages as $language){
			$this->languages->add($language);
		}
	}

	/**
	 * @var Document[]
	 */
	private $sampleReports;
	public function getSampleReports() { return $this->sampleReports; }

	/**
	 * @param Document[] $documents
	 */
	public function setSampleReports(array $documents)
	{
		$this->handleUsageOfMultipleDocuments($this->getSampleReports(), $documents);

		$this->sampleReports->clear();

		foreach ($documents as $document){
			$this->sampleReports->add($document);
		}
	}

	/**
	 * @var AscAppraiser[]
	 */
	private $relationsWithAscAppraisers;

	/**
	 * @param AscAppraiser $appraiser
	 */
	public function addRelationWithAscAppraiser(AscAppraiser $appraiser)
	{
		$this->relationsWithAscAppraisers->add($appraiser);
		$appraiser->setAppraiser($this);
	}

	/**
	 * @param AscAppraiser $appraiser
	 */
	public function removeRelationWithAscAppraiser(AscAppraiser $appraiser)
	{
		$this->relationsWithAscAppraisers->removeElement($appraiser);
		$appraiser->setAppraiser(null);
	}

	/**
	 * @var Customer[]|Traversable
	 */
	private $customers;
	public function getCustomers() { return $this->customers; }

	/**
	 * @param Customer $customer
	 */
	public function addCustomer(Customer $customer)
	{
		$this->customers->add($customer);
	}

	/**
	 * @var State
	 */
	private $assignmentState;
	public function setAssignmentState(State $state) { $this->assignmentState = $state; }
	public function getAssignmentState() { return $this->assignmentState; }

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
	 * @var EoEx
	 */
	private $eo;
	public function setEo(EoEx $eo) { $this->eo = $eo; }
	public function getEo() { return $this->eo; }


	/**
	 * @var Qualifications
	 */
	private $qualifications;
	public function setQualifications(Qualifications $qualifications) { $this->qualifications = $qualifications; }
	public function getQualifications() { return $this->qualifications; }

	/**
	 * @var Availability
	 */
	private $availability;
	public function setAvailability(Availability $availability) { $this->availability = $availability; }
	public function getAvailability() { return $this->availability; }

	/**
	 * @var License[]
	 */
	private $licenses;
	public function addLicense(License $license) { $this->licenses->add($license); }
	public function removeLicense(License $license) { $this->licenses->removeElement($license); }

	/**
	 * @var DefaultFee[]
	 */
	private $defaultFees;
	public function addDefaultFee(DefaultFee $fee) { $this->defaultFees->add($fee); }
	public function removeDefaultFee(DefaultFee $fee) { $this->defaultFees->removeElement($fee); }

	/**
	 * @var bool
	 */
	private $isRegistered;
	public function setRegistered($flag) { $this->isRegistered = $flag; }
	public function isRegistered() { return $this->isRegistered; }

	/**
	 * @return string
	 */
	public function getDisplayName()
	{
		return $this->getFirstName().' '.$this->getLastName();
	}

    /**
     * @var Ach
     */
	private $ach;
    public function setAch(Ach $ach) { $this->ach = $ach; }
    public function getAch() { return $this->ach; }

    public function __construct()
    {
		parent::__construct();

        $this->languages = new ArrayCollection();
        $this->sampleReports = new ArrayCollection();
		$this->relationsWithAscAppraisers = new ArrayCollection();
		$this->customers = new ArrayCollection();
		$this->licenses = new ArrayCollection();
		$this->defaultFees = new ArrayCollection();

		$this->setShowInitialDisplay(true);
    }
}
