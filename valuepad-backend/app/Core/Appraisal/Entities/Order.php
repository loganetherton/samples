<?php
namespace ValuePad\Core\Appraisal\Entities;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Traversable;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Amc\Entities\Item;
use ValuePad\Core\Appraisal\Enums\ConcessionUnit;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Enums\Property\ApproachesToBeIncluded;
use ValuePad\Core\Appraisal\Enums\Property\ContactType;
use ValuePad\Core\Appraisal\Enums\ValueQualifiers;
use ValuePad\Core\Appraisal\Enums\Workflow;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\Company\Entities\Staff;
use ValuePad\Core\Customer\Entities\AdditionalStatus;
use ValuePad\Core\Customer\Entities\Client;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\JobType;
use ValuePad\Core\Customer\Entities\Ruleset;
use ValuePad\Core\Customer\Enums\Rule;
use ValuePad\Core\Invitation\Entities\Invitation;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\User\Entities\User;

class Order
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
	private $fileNumber;
	public function setFileNumber($fileNumber) { $this->fileNumber = $fileNumber; }
	public function getFileNumber() { return $this->fileNumber; }

    /**
     * @var string
     */
	private $intendedUse;
	public function setIntendedUse($value) { $this->intendedUse = $value; }
	public function getIntendedUse() { return $this->intendedUse; }

	/**
	 * @var string
	 */
	private $referenceNumber;
	public function setReferenceNumber($number) { $this->referenceNumber = $number; }
	public function getReferenceNumber() { return $this->referenceNumber; }

	/**
	 * @return string
	 */
	public function getClientName() { return $this->getClient()->getName(); }

	/**
	 * @return string
	 */
	public function getClientAddress1()
    {
        return $this->getRuleValueOrResolveDefault(
            Rule::CLIENT_ADDRESS_1,
            function(){ return $this->getClient()->getAddress1(); }
        );
    }

	/**
	 * @return string
	 */
	public function getClientAddress2()
    {
        return $this->getRuleValueOrResolveDefault(
            Rule::CLIENT_ADDRESS_2,
            function(){ return $this->getClient()->getAddress2(); }
        );
    }

	/**
	 * @return string
	 */
	public function getClientZip()
    {
        return $this->getRuleValueOrResolveDefault(
            Rule::CLIENT_ZIP,
            function(){ return $this->getClient()->getZip(); }
        );
    }

	/**
	 * @return string
	 */
	public function getClientCity()
    {
        return $this->getRuleValueOrResolveDefault(
            Rule::CLIENT_CITY,
            function(){ return $this->getClient()->getCity(); }
        );
    }

	/**
	 * @return State
	 */
	public function getClientState()
    {
        return $this->getRuleValueOrResolveDefault(
            Rule::CLIENT_STATE,
            function(){ return $this->getClient()->getState(); }
        );
    }

	/**
	 * @return string
	 */
	public function getClientDisplayedOnReportName() { return $this->getClientDisplayedOnReport()->getName(); }

	/**
	 * @return string
	 */
	public function getClientDisplayedOnReportCity()
    {
        return $this->getRuleValueOrResolveDefault(
            Rule::CLIENT_DISPLAYED_ON_REPORT_CITY,
            function(){ return $this->getClientDisplayedOnReport()->getCity(); }
        );
    }

	/**
	 * @return string
	 */
	public function getClientDisplayedOnReportZip()
    {
        return $this->getRuleValueOrResolveDefault(
            Rule::CLIENT_DISPLAYED_ON_REPORT_ZIP,
            function(){ return $this->getClientDisplayedOnReport()->getZip(); }
        );
    }

	/**
	 * @return string
	 */
	public function getClientDisplayedOnReportAddress1()
    {
        return $this->getRuleValueOrResolveDefault(
            Rule::CLIENT_DISPLAYED_ON_REPORT_ADDRESS_1,
            function(){ return $this->getClientDisplayedOnReport()->getAddress1(); }
        );
    }

	/**
	 * @return string
	 */
	public function getClientDisplayedOnReportAddress2()
    {
        return $this->getRuleValueOrResolveDefault(
            Rule::CLIENT_DISPLAYED_ON_REPORT_ADDRESS_2,
            function(){ return $this->getClientDisplayedOnReport()->getAddress2(); }
        );
    }

	/**
	 * @return State
	 */
	public function getClientDisplayedOnReportState()
    {
        return $this->getRuleValueOrResolveDefault(
            Rule::CLIENT_DISPLAYED_ON_REPORT_STATE,
            function(){ return $this->getClientDisplayedOnReport()->getState(); }
        );
    }

	/**
	 * @var string
	 */
	private $amcLicenseNumber;
	public function setAmcLicenseNumber($number) { $this->amcLicenseNumber = $number; }
	public function getAmcLicenseNumber() { return $this->amcLicenseNumber; }

	/**
	 * @var DateTime
	 */
	private $amcLicenseExpiresAt;
	public function setAmcLicenseExpiresAt(DateTime $expiresAt = null) { $this->amcLicenseExpiresAt = $expiresAt; }
	public function getAmcLicenseExpiresAt() { return $this->amcLicenseExpiresAt; }

	/**
	 * @var float
	 */
	private $fee;
	public function setFee($fee) { $this->fee = $fee; }
	public function getFee() { return $this->fee; }

	/**
	 * @var float
	 */
	private $techFee;
	public function setTechFee($fee) { $this->techFee = $fee; }
	public function getTechFee() { return $this->techFee; }

	/**
	 * @var float
	 */
	private $purchasePrice;
	public function setPurchasePrice($price) { $this->purchasePrice = $price; }
	public function getPurchasePrice() { return $this->purchasePrice; }

	/**
	 * @var string
	 */
	private $fhaNumber;
	public function setFhaNumber($number) { $this->fhaNumber = $number; }
	public function getFhaNumber() { return $this->fhaNumber; }

	/**
	 * @var string
	 */
	private $loanNumber;
	public function setLoanNumber($number) { $this->loanNumber = $number; }
	public function getLoanNumber() { return $this->loanNumber; }

	/**
	 * @var string
	 */
	private $loanType;
	public function setLoanType($loanType) { $this->loanType = $loanType; }
	public function getLoanType() { return $this->loanType; }

	/**
	 * @var float
	 */
	private $loanAmount;
	public function setLoanAmount($loanAmount) { $this->loanAmount = $loanAmount; }
	public function getLoanAmount() { return $this->loanAmount; }

	/**
	 * @var float
	 */
	private $concession;
	public function getConcession() { return $this->concession; }
	public function setConcession($concession) { $this->concession = $concession; }

	/**
	 * @var ConcessionUnit
	 */
	private $concessionUnit;
	public function getConcessionUnit() { return $this->concessionUnit; }
	public function setConcessionUnit(ConcessionUnit $unit = null) { $this->concessionUnit = $unit; }

	/**
	 * @var float
	 */
	private $salesPrice;
	public function getSalesPrice() { return $this->salesPrice; }
	public function setSalesPrice($price) { $this->salesPrice = $price; }

	/**
	 * @var DateTime
	 */
	private $contractDate;
	public function getContractDate() { return $this->contractDate; }
	public function setContractDate(DateTime $contractDate = null) { $this->contractDate = $contractDate; }

	/**
	 * @var ApproachesToBeIncluded
	 */
	private $approachesToBeIncluded;
	public function setApproachesToBeIncluded(ApproachesToBeIncluded $approaches) { $this->approachesToBeIncluded = $approaches; }
	public function getApproachesToBeIncluded() { return $this->approachesToBeIncluded; }

	/**
	 * @var DateTime
	 */
	private $dueDate;
	public function setDueDate(DateTime $datetime = null) { $this->dueDate = $datetime; }
	public function getDueDate() { return $this->dueDate; }

	/**
	 * @var DateTime
	 */
	private $orderedAt;
	public function setOrderedAt(DateTime $datetime) { $this->orderedAt = $datetime; }
	public function getOrderedAt() { return $this->orderedAt; }

	/**
	 * @var DateTime
	 */
	private $estimatedCompletionDate;
	public function setEstimatedCompletionDate(DateTime $datetime = null) { $this->estimatedCompletionDate = $datetime; }
	public function getEstimatedCompletionDate() { return $this->estimatedCompletionDate; }

	/**
	 * @var DateTime
	 */
	private $paidAt;
	public function setPaidAt(DateTime $datetime = null) { $this->paidAt = $datetime; }
	public function getPaidAt() { return $this->paidAt; }

	/**
	 * @var string
	 */
	private $instruction;
	public function setInstruction($instruction) { $this->instruction = $instruction; }
	public function getInstruction() { return $this->instruction; }

	/**
	 * @var JobType
	 */
	private $jobType;
	public function setJobType(JobType $jobType) { $this->jobType = $jobType; }
	public function getJobType() { return $this->jobType; }

	/**
	 * @var JobType[]|Traversable
	 */
	private $additionalJobTypes;
	public function getAdditionalJobTypes() { return $this->additionalJobTypes; }
	public function clearAdditionalJobTypes() { $this->additionalJobTypes->clear(); }

	/**
	 * @param JobType[] $jobTypes
	 */
	public function setAdditionalJobTypes($jobTypes)
	{
		$this->additionalJobTypes->clear();

		foreach ($jobTypes as $jobType){
			$this->additionalJobTypes->add($jobType);
		}
	}

	/**
	 * @var Appraiser|Amc|Manager
	 */
	private $assignee;
	public function setAssignee(User $assignee) { $this->assignee = $assignee; }
	public function getAssignee() { return $this->assignee; }

    /**
     * @var Staff
     */
    private $staff;
    public function getStaff() { return $this->staff; }
    public function setStaff(Staff $staff) {$this->staff = $staff; }

    /**
     * @return Company
     */
    public function getCompany() { return object_take($this->getStaff(), 'company'); }

	/**
	 * @var Customer
	 */
	private $customer;
	public function setCustomer(Customer $customer) {$this->customer = $customer; }
	public function getCustomer() { return $this->customer; }

    /**
     * @var bool
     */
    private $isRush = false;
    public function isRush() { return $this->isRush; }
    public function setRush($flag) { $this->isRush = $flag; }

	/**
	 * @var bool
	 */
	private $isPaid;
	public function isPaid() { return $this->isPaid; }
	public function setPaid($flag) { $this->isPaid = $flag; }

	/**
	 * @var DateTime
	 */
	private $assignedAt;
	public function setAssignedAt(DateTime $datetime) { $this->assignedAt = $datetime; }
	public function getAssignedAt() { return $this->assignedAt; }

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

	/**
	 * @var Client
	 */
	private $client;
	public function setClient(Client $client) { $this->client = $client; }
	public function getClient() { return $this->client; }

	/**
	 * @var Client
	 */
	private $clientDisplayedOnReport;
	public function setClientDisplayedOnReport(Client $client) { $this->clientDisplayedOnReport = $client; }
	public function getClientDisplayedOnReport() { return $this->clientDisplayedOnReport; }

	/**
	 * @var DateTime
	 */
	private $completedAt;
	public function setCompletedAt(DateTime $datetime) { $this->completedAt = $datetime; }
	public function getCompletedAt() { return $this->completedAt; }

	/**
	 * @var DateTime
	 */
	private $acceptedAt;
	public function setAcceptedAt(DateTime $datetime) { $this->acceptedAt = $datetime; }
	public function getAcceptedAt() { return $this->acceptedAt; }

	/**
	 * @var DateTime
	 */
	private $inspectionScheduledAt;
	public function setInspectionScheduledAt(DateTime $datetime = null) { $this->inspectionScheduledAt = $datetime; }
	public function getInspectionScheduledAt() { return $this->inspectionScheduledAt; }

	/**
	 * @var DateTime
	 */
	private $inspectionCompletedAt;
	public function setInspectionCompletedAt(DateTime $datetime = null) { $this->inspectionCompletedAt = $datetime; }
	public function getInspectionCompletedAt() { return $this->inspectionCompletedAt; }

	/**
	 * @var DateTime
	 */
	private $putOnHoldAt;
	public function setPutOnHoldAt(DateTime $dateTime) { $this->putOnHoldAt = $dateTime; }
	public function getPutOnHoldAt() { return $this->putOnHoldAt; }


	/**
	 * @var DateTime
	 */
	private $revisionReceivedAt;
	public function setRevisionReceivedAt(DateTime $datetime) { $this->revisionReceivedAt = $datetime; }
	public function getRevisionReceivedAt() { return $this->revisionReceivedAt; }

    /**
     * @var Fdic
     */
    private $fdic;
    public function setFdic(Fdic $fdic = null) { $this->fdic = $fdic; }
    public function getFdic() { return $this->fdic; }

	/**
	 * @var Property
	 */
	private $property;
	public function setProperty(Property $property) { $this->property = $property; }
	public function getProperty() { return $this->property; }

	/**
	 * @var ExternalDocument[]
	 */
	private $instructionDocuments;
	public function getInstructionDocuments() { return $this->instructionDocuments; }

	public function clearInstructionDocuments()
	{
		$this->instructionDocuments->clear();
	}

	/**
	 * @param InstructionExternalDocument $document
	 */
	public function addInstructionDocument(InstructionExternalDocument $document)
	{
		$document->setOrder($this);
		$this->instructionDocuments->add($document);
	}

	/**
	 * @return bool
	 */
	public function hasInstructionDocuments()
	{
		return (bool) $this->instructionDocuments->count();
	}

	/**
	 * @var ExternalDocument[]
	 */
	private $additionalDocuments;
	public function getAdditionalDocuments() { return $this->additionalDocuments; }
	public function clearAdditionalDocuments() { $this->additionalDocuments->clear(); }

	/**
	 * @param AdditionalExternalDocument $document
	 */
	public function addAdditionalDocument(AdditionalExternalDocument $document)
	{
		$document->setOrder($this);
		$this->additionalDocuments->add($document);
	}

	/**
	 * @return bool
	 */
	public function hasAdditionalDocuments()
	{
		return (bool) $this->additionalDocuments->count();
	}

	/**
	 * @var Workflow
	 */
	private $workflow;
	public function setWorkflow(Workflow $workflow) { $this->workflow = $workflow; }
	public function getWorkflow() { return $this->workflow; }

	/**
	 * @var ProcessStatus
	 */
	private $processStatus;
	public function getProcessStatus() { return $this->processStatus; }

	/**
	 * @param ProcessStatus $processStatus
	 */
	public function setProcessStatus(ProcessStatus $processStatus)
	{
		$this->processStatus = $processStatus;

		if (!$this->getWorkflow()->has($processStatus)){
			$workflow = new Workflow($this->getWorkflow());
			$workflow->push($processStatus);
			$this->setWorkflow($workflow);
		}
		// Set tax ID at completion
		if ($processStatus->is(ProcessStatus::COMPLETED)) {
			$this->setTinAtCompletion($this->getAssigneeTaxId());
		}
	}

	/**
	 * @var string
	 */
	private $comment;
	public function setComment($comment) { $this->comment = $comment; }
	public function getComment() { return $this->comment; }


	/**
	 * @var Bid
	 */
	private $bid;

    /**
     * @return Bid
     */
	public function getBid() { return $this->bid->first()?:null; }

	/**
	 * @param Bid $bid
	 */
	public function setBid(Bid $bid = null)
	{
		$this->bid->clear();

		if ($bid !== null){
			$this->bid->add($bid);
		}
	}

	/**
	 * @var AdditionalDocument
	 */
	private $contractDocument;
	public function setContractDocument(AdditionalDocument $document = null) { $this->contractDocument = $document; }
	public function getContractDocument() { return $this->contractDocument; }


	/**
	 * @var bool
	 */
	private $isTechFeePaid = false;
	public function setTechFeePaid($flag) { $this->isTechFeePaid = $flag; }
	public function isTechFeePaid() { return $this->isTechFeePaid; }

    /**
     * @return bool
     */
	public function needToPayTechFee() { return $this->isTechFeePaid() === false && $this->getTechFee() !== null; }

	/**
	 * @var AdditionalStatus
	 */
	private $additionalStatus;
	public function setAdditionalStatus(AdditionalStatus $additionalStatus) { $this->additionalStatus = $additionalStatus; }
	public function getAdditionalStatus() { return $this->additionalStatus; }


	/**
	 * @var string
	 */
	private $additionalStatusComment;
	public function setAdditionalStatusComment($comment) { $this->additionalStatusComment = $comment; }
	public function getAdditionalStatusComment() { return $this->additionalStatusComment; }

	/**
	 * @var Invitation
	 */
	private $invitation;
	public function setInvitation(Invitation $invitation = null) { $this->invitation = $invitation; }
	public function getInvitation() { return $this->invitation; }

	/**
	 * @var AcceptedConditions
	 */
	private $acceptedConditions;
	public function setAcceptedConditions(AcceptedConditions $acceptedConditions) { $this->acceptedConditions = $acceptedConditions; }
	public function getAcceptedConditions() { return $this->acceptedConditions; }

	/**
	 * @var Ruleset[]|Traversable
	 */
	private $rulesets;
	public function getRulesets() { return $this->rulesets; }

	/**
	 * @param Ruleset[] $rulesets
	 */
	public function setRulesets(array $rulesets)
	{
		$this->rulesets->clear();

		foreach ($rulesets as $ruleset){
			$this->rulesets->add($ruleset);
		}
	}

	/**
	 * @return array
	 */
	public function getRules()
	{
		$rulesets = iterator_to_array($this->getRulesets());

		usort($rulesets, function(Ruleset $a, Ruleset $b){
			if ($a->getLevel() > $b->getLevel()){
				return 1;
			}

			if ($a->getLevel() < $b->getLevel()){
				return -1;
			}

			return 0;
		});

		$rules = [];

		foreach ($rulesets as $ruleset){
			$rules = array_merge($rules, $ruleset->getRules());
		}

		return $rules;
	}

	/**
	 * @return Contact
	 */
	public function getBorrower()
	{
		$contacts = $this->getProperty()->getContacts();

		foreach ($contacts as $contact){
			if ($contact->getType()->is(ContactType::BORROWER)){
				return $contact;
			}
		}

		return null;
	}

    /**
     * @var string
     */
	private $lienPosition;
    public function setLienPosition($position) { $this->lienPosition = $position; }
    public function getLienPosition() { return $this->lienPosition; }


    /**
     * @var ValueQualifiers
     */
    private $valueQualifiers;
    public function setValueQualifiers(ValueQualifiers $qualifiers) { $this->valueQualifiers = $qualifiers; }
    public function getValueQualifiers() { return $this->valueQualifiers; }

    /**
     * @var SupportingDetails
     */
    private $supportingDetails;
    public function setSupportingDetails(SupportingDetails $details) { $this->supportingDetails = $details; }
    public function getSupportingDetails() { return $this->supportingDetails; }


	public function __construct()
	{
		$this->instructionDocuments = new ArrayCollection();
		$this->additionalDocuments = new ArrayCollection();
		$this->additionalJobTypes = new ArrayCollection();
		$this->workflow = new Workflow();
		$this->isPaid = false;
		$this->approachesToBeIncluded = new ApproachesToBeIncluded();
		$this->bid = new ArrayCollection();
		$this->rulesets = new ArrayCollection();
		$this->createdAt = new DateTime();
		$this->updatedAt = new DateTime();
        $this->setValueQualifiers(new ValueQualifiers());
	}

    /**
     * @param string $rule
     * @param mixed $resolver
     * @return mixed
     */
    private function getRuleValueOrResolveDefault($rule, $resolver)
    {
        $rules = $this->getRules();

        if (array_key_exists($rule, $rules)){
            return $rules[$rule];
        }

        return $resolver();
    }

    /**
     * This only makes sense in the context of AMC, because as of right now
     * they're the only one who's got invoices. Additionally, this is somewhat
     * of a hack for being able to query orders in Doctrine with no invoice items
     * associated to it.
     *
     * @var Item
     */
    private $invoiceItem;
    public function setInvoiceItem(Item $invoiceItem) { $this->invoiceItem = $invoiceItem; }
    public function getInvoiceItem() { return $this->invoiceItem; }

    /**
     * @var tinAtCompletion
     * Tax ID at the time of order completion
     */
	private $tinAtCompletion;
    public function setTinAtCompletion($tinAtCompletion) { $this->tinAtCompletion = $tinAtCompletion; }
    public function getTinAtCompletion() { return $this->tinAtCompletion; }

    /**
     * Get tax ID of assignee at the time order is completed
     * @return string        Tax ID
     */
    public function getAssigneeTaxId() {
        $staff = $this->getStaff();
        $assignee = $this->getAssignee();
        $tin = '';
        // Don't overwrite an existing TIN
        if ($this->getTinAtCompletion()) {
            return $this->getTinAtCompletion();
        }

        if ($staff) {
        	$tin = $staff->getCompany()->getTaxId();
        } else if ($assignee instanceof Appraiser) {
        	$tin = $assignee->getTaxIdentificationNumber();
        }

        return $tin;
    }

    /**
     * For large commercial projects, the order might be handled by more than one appraiser
     *
     * @var Appraiser[]
     */
    private $subAssignees;
    public function setSubAssignees($subAssignees) { $this->subAssignees = $subAssignees; }
    public function getSubAssignees() { return $this->subAssignees; }

}
