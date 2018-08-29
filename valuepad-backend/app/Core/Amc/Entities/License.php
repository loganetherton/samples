<?php
namespace ValuePad\Core\Amc\Entities;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use ValuePad\Core\Assignee\Interfaces\CoverageInterface;
use ValuePad\Core\Assignee\Interfaces\CoverageStorableInterface;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Support\DocumentUsageManagementTrait;
use ValuePad\Core\Location\Entities\State;

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
    private $number = '';
    public function setNumber($number) { $this->number = $number; }
    public function getNumber() { return $this->number; }

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
     * @var Amc
     */
    private $amc;
    public function setAmc(Amc $amc) { $this->amc = $amc; }
    public function getAmc() { return $this->amc; }

    /**
     * @var Alias
     */
    private $alias;
    public function setAlias(Alias $alias) { $this->alias = $alias; }
    public function getAlias() { return $this->alias; }
    public function removeAlias() { $this->alias = null; }

    public function __construct()
    {
        $this->coverages = new ArrayCollection();
    }
}
