<?php
namespace ValuePad\Core\Amc\Entities;

abstract class AbstractFeeByZip
{
    /**
     * @var int
     */
    protected $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var string
     */
    protected $zip;
    public function setZip($zip) { $this->zip = $zip; }
    public function getZip() { return $this->zip; }

    /**
     * @var float
     */
    protected $amount;
    public function setAmount($amount) { $this->amount = $amount; }
    public function getAmount() { return $this->amount; }
}
