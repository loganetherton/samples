<?php
namespace ValuePad\Core\Amc\Entities;
use ValuePad\Core\Appraisal\Entities\Order;
use DateTime;

class Item
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var float
     */
    private $amount;
    public function setAmount($amount) { $this->amount = $amount; }
    public function getAmount() { return $this->amount; }

    /**
     * @var string
     */
    private $fileNumber;
    public function setFileNumber($fileNumber) { $this->fileNumber = $fileNumber; }
    public function getFileNumber() { return $this->fileNumber; }

    /**
     * @var string
     */
    private $jobType;
    public function setJobType($title) { $this->jobType = $title; }
    public function getJobType() { return $this->jobType; }

    /**
     * @var string
     */
    private $loanNumber;
    public function setLoanNumber($number) { $this->loanNumber = $number; }
    public function getLoanNumber() { return $this->loanNumber; }

    /**
     * @var string
     */
    private $borrowerName;
    public function setBorrowerName($borrowerName) { $this->borrowerName = $borrowerName; }
    public function getBorrowerName() { return $this->borrowerName; }

    /**
     * @var string
     */
    private $address;
    public function setAddress($address) { $this->address = $address; }
    public function getAddress() { return $this->address; }

    /**
     * @var DateTime
     */
    private $orderedAt;
    public function setOrderedAt(DateTime $datetime){ $this->orderedAt = $datetime; }
    public function getOrderedAt() { return $this->orderedAt; }

    /**
     *  DateTime
     */
    private $completedAt;
    public function setCompletedAt(DateTime $datetime) { $this->completedAt = $datetime; }
    public function getCompletedAt() { return $this->completedAt; }

    /**
     * @var Invoice
     */
    private $invoice;
    public function getInvoice() { return $this->invoice; }
    public function setInvoice(Invoice $invoice) { $this->invoice = $invoice; }

    /**
     * @var Order
     */
    private $order;
    public function setOrder(Order $order) { $this->order = $order; }
    public function getOrder() { return $this->order; }
}
