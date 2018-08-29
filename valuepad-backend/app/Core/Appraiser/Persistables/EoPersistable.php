<?php
namespace ValuePad\Core\Appraiser\Persistables;

use DateTime;
use ValuePad\Core\Document\Persistables\Identifier;

class EoPersistable
{
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
     * @var Identifier
     */
    private $document;
    public function getDocument() { return $this->document; }
    public function setDocument(Identifier $document) { $this->document = $document; }
}
