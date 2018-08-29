<?php
namespace ValuePad\Core\Appraisal\Persistables;

use ValuePad\Core\Appraisal\Enums\ConcessionUnit;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Appraisal\Enums\Property\ApproachesToBeIncluded;
use DateTime;
use ValuePad\Core\Appraisal\Enums\ValueQualifiers;

abstract class AbstractOrderPersistable
{
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
	 * @var int
	 */
	private $client;
	public function setClient($client) { $this->client = $client; }
	public function getClient() { return $this->client; }

	/**
	 * @var int
	 */
	private $clientDisplayedOnReport;
	public function setClientDisplayedOnReport($client) { $this->clientDisplayedOnReport = $client; }
	public function getClientDisplayedOnReport() { return $this->clientDisplayedOnReport; }

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
	 * @var DateTime
	 */
	private $paidAt;
	public function setPaidAt(DateTime $datetime = null) { $this->paidAt = $datetime; }
	public function getPaidAt() { return $this->paidAt; }

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
	 * @var string
	 */
	private $instruction;
	public function setInstruction($instruction) { $this->instruction = $instruction; }
	public function getInstruction() { return $this->instruction; }

	/**
	 * @var int
	 */
	private $jobType;
	public function setJobType($id) { $this->jobType = $id; }
	public function getJobType() { return $this->jobType; }

    /**
     * @var bool
     */
    private $isRush;
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
    private $estimatedCompletionDate;
    public function setEstimatedCompletionDate(DateTime $datetime = null) { $this->estimatedCompletionDate = $datetime; }
    public function getEstimatedCompletionDate() { return $this->estimatedCompletionDate; }

    /**
     * @var FdicPersistable
     */
	private $fdic;
    public function setFdic(FdicPersistable $fdic) { $this->fdic = $fdic; }
    public function getFdic() { return $this->fdic; }

	/**
	 * @var PropertyPersistable
	 */
	private $property;
	public function setProperty(PropertyPersistable $property) { $this->property = $property; }
	public function getProperty() { return $this->property; }

	/**
	 * @var ExternalDocumentPersistable[]
	 */
	private $instructionDocuments;
	public function setInstructionDocuments(array $documents) { $this->instructionDocuments = $documents; }
	public function getInstructionDocuments() { return $this->instructionDocuments; }

	/**
	 * @var ExternalDocumentPersistable[]
	 */
	private $additionalDocuments;
	public function setAdditionalDocuments(array $documents) { $this->additionalDocuments = $documents; }
	public function getAdditionalDocuments() { return $this->additionalDocuments; }

	/**
	 * @var ProcessStatus
	 */
	private $processStatus;
	public function setProcessStatus(ProcessStatus $processStatus) { $this->processStatus = $processStatus; }
	public function getProcessStatus() { return $this->processStatus; }

	/**
	 * @var array
	 */
	private $additionalJobTypes = [];
	public function setAdditionalJobTypes(array $jobTypes) { $this->additionalJobTypes = $jobTypes; }
	public function getAdditionalJobTypes() { return $this->additionalJobTypes; }

	/**
	 * @var AcceptedConditionsPersistable
	 */
	private $acceptedConditions;
	public function setAcceptedConditions(AcceptedConditionsPersistable $persistable) { $this->acceptedConditions = $persistable; }
	public function getAcceptedConditions() { return $this->acceptedConditions; }

	/**
	 * @var array
	 */
	private $rulesets;
	public function setRulesets(array $rulesets) { $this->rulesets = $rulesets; }
	public function getRulesets() { return $this->rulesets; }

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
}
