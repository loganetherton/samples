<?php
namespace ValuePad\Core\Amc\Persistables;

use DateTime;
use ValuePad\Core\Amc\Entities\Coverage;
use ValuePad\Core\Assignee\Support\CoverageManagement;
use ValuePad\Core\Document\Persistables\Identifier;

class LicensePersistable
{
    /**
     * @var string
     */
    private $number;
    public function setNumber($number) { $this->number = $number; }
    public function getNumber() { return $this->number; }

    /**
     * @var string
     */
    private $state;
    public function setState($state) { $this->state = $state; }
    public function getState() { return $this->state; }

    /**
     * @var DateTime
     */
    private $expiresAt;
    public function setExpiresAt(DateTime $datetime) { $this->expiresAt = $datetime; }
    public function getExpiresAt() { return $this->expiresAt; }

    /**
     * @var Identifier
     */
    private $document;
    public function setDocument(Identifier $identifier) { $this->document = $identifier; }
    public function getDocument() { return $this->document; }

    /**
     *
     * @var CoveragePersistable[]
     */
    private $coverages;
    public function setCoverages(array $coverages) { $this->coverages = $coverages; }
    public function getCoverages() { return $this->coverages; }

    /**
     * @param Coverage[] $coverages
     */
    public function adaptCoverages($coverages)
    {
        $this->setCoverages(CoverageManagement::asPersistables($coverages, CoveragePersistable::class));
    }

    /**
     * @var AliasPersistable
     */
    private $alias;
    public function setAlias(AliasPersistable $alias = null) { $this->alias = $alias; }
    public function getAlias() { return $this->alias; }
}
