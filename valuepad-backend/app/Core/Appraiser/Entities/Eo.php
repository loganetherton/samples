<?php
namespace ValuePad\Core\Appraiser\Entities;

use ValuePad\Core\Document\Support\DocumentUsageManagementTrait;
use ValuePad\Core\Document\Entities\Document;
use DateTime;

class Eo
{
    use DocumentUsageManagementTrait;

    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var float
     */
    private $claimAmount;
    public function getClaimAmount() { return $this->claimAmount; }
    public function setClaimAmount($claimAmount) { $this->claimAmount = $claimAmount; }

    /**
     * @var float
     */
    private $aggregateAmount;
    public function getAggregateAmount() { return $this->aggregateAmount; }
    public function setAggregateAmount($aggregateAmount) { $this->aggregateAmount = $aggregateAmount; }

    /**
     * @var DateTime
     */
    private $expiresAt;
    public function getExpiresAt() { return $this->expiresAt; }
    public function setExpiresAt(DateTime $expiresAt) { $this->expiresAt = $expiresAt; }

    /**
     * @var string
     */
    private $carrier;
    public function getCarrier() { return $this->carrier; }
    public function setCarrier($carrier) { $this->carrier = $carrier; }

    /**
     * @var float
     */
    private $deductible;
    public function getDeductible() { return $this->deductible; }
    public function setDeductible($deductible) { $this->deductible = $deductible; }

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
}
