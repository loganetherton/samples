<?php
namespace ValuePad\Core\Amc\Persistables;

class FeeByZipPersistable
{
    /**
     * @var string
     */
    private $zip;
    public function setZip($zip) { $this->zip = $zip; }
    public function getZip() { return $this->zip; }

    /**
     * @var float
     */
    private $amount;
    public function setAmount($amount) { $this->amount = $amount; }
    public function getAmount() { return $this->amount; }
}
