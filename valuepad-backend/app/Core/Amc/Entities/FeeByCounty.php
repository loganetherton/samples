<?php
namespace ValuePad\Core\Amc\Entities;

class FeeByCounty extends AbstractFeeByCounty
{
    /**
     * @var Fee
     */
    private $fee;
    public function setFee(Fee $fee) { $this->fee = $fee; }
    public function getFee() { return $this->fee; }
}
