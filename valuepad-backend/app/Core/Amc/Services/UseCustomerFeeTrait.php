<?php
namespace ValuePad\Core\AMc\Services;

use ValuePad\Core\Assignee\Entities\CustomerFee;

trait UseCustomerFeeTrait
{
    /**
     * @return string
     */
    protected function getFeeClass()
    {
        return CustomerFee::class;
    }
}
