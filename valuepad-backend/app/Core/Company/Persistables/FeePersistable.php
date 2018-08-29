<?php
namespace ValuePad\Core\Company\Persistables;

class FeePersistable
{
    /**
     * @var int
     */
    private $jobType;
    public function setJobType($jobType) { $this->jobType = $jobType; }
    public function getJobType() { return $this->jobType; }

    /**
     * @var float
     */
    private $amount;
    public function setAmount($amount)  { $this->amount = $amount; }
    public function getAmount() { return $this->amount; }
}
