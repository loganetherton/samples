<?php
namespace ValuePad\Core\Amc\Entities;

use ValuePad\Core\Assignee\Entities\CustomerFee;

class CustomerFeeByState extends AbstractFeeByState
{
    /**
     * @var CustomerFee
     */
    private $fee;
    public function setFee(CustomerFee $fee) { $this->fee = $fee; }
    public function getFee() { return $this->fee; }
}
