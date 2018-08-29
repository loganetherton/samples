<?php
namespace ValuePad\Core\Amc\Entities;
use ValuePad\Core\JobType\Entities\JobType;

class Fee
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var bool
     */
    private $isEnabled = true;
    public function setEnabled($flag) { $this->isEnabled = $flag; }
    public function isEnabled() { return $this->isEnabled; }

    /**
     * @var JobType
     */
    private $jobType;
    public function setJobType(JobType $jobType) { $this->jobType = $jobType; }
    public function getJobType() { return $this->jobType; }

    /**
     * @var float
     */
    private $amount;
    public function setAmount($amount) { $this->amount = $amount; }
    public function getAmount() { return $this->amount; }

    /**
     * @var Amc
     */
    private $amc;
    public function setAmc(Amc $amc) { $this->amc = $amc; }
    public function getAmc() { return $this->amc; }
}
