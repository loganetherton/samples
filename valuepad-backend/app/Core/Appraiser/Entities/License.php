<?php
namespace ValuePad\Core\Appraiser\Entities;

use ValuePad\Core\Asc\Enums\Certifications;
use ValuePad\Core\Assignee\Interfaces\CoverageInterface;
use ValuePad\Core\Assignee\Interfaces\CoverageStorableInterface;
use ValuePad\Core\Document\Support\DocumentUsageManagementTrait;
use ValuePad\Core\Location\Entities\State;
use Doctrine\Common\Collections\ArrayCollection;
use ValuePad\Core\Document\Entities\Document;
use DateTime;

class License implements CoverageStorableInterface
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
	private $number;
	public function setNumber($number) { $this->number = $number; }
	public function getNumber() { return $this->number; }

	/**
	 * @var Certifications
	 */
	private $certifications;
	public function setCertifications(Certifications $certifications) { $this->certifications = $certifications; }
	public function getCertifications() { return $this->certifications; }

	/**
	 * @var State
	 */
	private $state;
	public function setState(State $state) { $this->state = $state; }
	public function getState() { return $this->state; }

	/**
	 * @var DateTime
	 */
	private $expiresAt;
	public function setExpiresAt(DateTime $datetime) { $this->expiresAt = $datetime; }
	public function getExpiresAt() { return $this->expiresAt; }

	/**
	 * @var bool
	 */
	private $isFhaApproved;
	public function setFhaApproved($flag) { $this->isFhaApproved = $flag; }
	public function isFhaApproved() { return $this->isFhaApproved; }

	/**
	 * @var bool
	 */
	private $isCommercial;
	public function setCommercial($flag) { $this->isCommercial = $flag; }
	public function isCommercial() { return $this->isCommercial; }

    /**
     * @var Coverage[]|CoverageInterface[]|ArrayCollection
     */
    private $coverages;
	public function getCoverages() { return $this->coverages; }

	/**
	 * @param CoverageInterface|Coverage $coverage
	 */
	public function addCoverage(CoverageInterface $coverage) { $this->coverages->add($coverage); }
	public function clearCoverages() { $this->coverages->clear(); }
    /**
     * @var bool
     */
    private $isPrimary = false;
	public function setPrimary($flag) { $this->isPrimary = $flag; }
	public function isPrimary() { return $this->isPrimary; }

    /**
     * @var Document
     */
    private $document;
	public function getDocument() { return $this->document; }

	/**
	 * @param Document $document
	 */
	public function setDocument(Document $document)
	{
		$this->handleUsageOfOneDocument($this->getDocument(), $document);

		$this->document = $document;
	}

	public function detachDocument()
	{
		$this->handleUsageOfOneDocument($this->getDocument(), null);

		$this->document = null;
	}

	/**
	 * @var Appraiser
	 */
	private $appraiser;
	public function getAppraiser() { return $this->appraiser; }

	/**
	 * @param Appraiser $appraiser
	 */
	public function setAppraiser(Appraiser $appraiser)
	{
		$appraiser->addLicense($this);
		$this->appraiser = $appraiser;
	}

	public function __construct()
	{
		$this->coverages = new ArrayCollection();
		$this->isFhaApproved = false;
		$this->isCommercial = false;
	}
}
