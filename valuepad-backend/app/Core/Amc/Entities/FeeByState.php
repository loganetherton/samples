<?php
namespace ValuePad\Core\Amc\Entities;

class FeeByState extends AbstractFeeByState
{
    /**
     * @var Fee
     */
    private $fee;
    public function setFee(Fee $fee) { $this->fee = $fee; }
    public function getFee() { return $this->fee; }
}
