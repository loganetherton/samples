<?php
namespace ValuePad\Core\Amc\Entities;

use ValuePad\Core\Assignee\Entities\CustomerFee;

class CustomerFeeByZip extends AbstractFeeByZip
{
    /**
     * @var CustomerFee
     */
    private $fee;
    public function setFee(CustomerFee $fee) { $this->fee = $fee; }
    public function getFee() { return $this->fee; }
}
