<?php
namespace ValuePad\Core\Amc\Entities;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Support\DocumentUsageManagementTrait;

class Invoice
{
    use DocumentUsageManagementTrait;

    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var Item[]
     */
    private $items;
    public function getItems() { return $this->items; }

    /**
     * @param Item[] $items
     */
    public function setItems($items)
    {
        $this->items->clear();

        foreach ($items as $item){
            $this->items->add($item);
        }
    }

    /**
     * @var Amc
     */
    private $amc;
    public function setAmc(Amc $amc) { $this->amc = $amc; }
    public function getAmc() { return $this->amc; }

    /**
     * @var DateTime
     */
    private $to;
    public function setTo(DateTime $datetime) { $this->to = $datetime; }
    public function getTo() { return $this->to; }

    /**
     * @var DateTime
     */
    private $from;
    public function setFrom(DateTime $datetime) { $this->from = $datetime; }
    public function getFrom() { return $this->from; }

    /**
     * @var bool
     */
    private $isPaid = false;
    public function setPaid($flag) { $this->isPaid = $flag; }
    public function isPaid() { return $this->isPaid; }

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

    /**
     * @var DateTime
     */
    private $createdAt;
    public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
    public function getCreatedAt() { return $this->createdAt; }


    /**
     * @var float
     */
    private $amount;
    public function setAmount($amount) { $this->amount = $amount; }
    public function getAmount() { return $this->amount; }

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->setCreatedAt(new DateTime());
    }
}
