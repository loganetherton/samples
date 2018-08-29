<?php
namespace ValuePad\Core\Amc\Services;

use ValuePad\Core\Amc\Entities\CustomerFeeByState;

trait UseCustomerFeeByStateTrait
{
    /**
     * @return string
     */
    protected function getFeeByStateClass()
    {
        return CustomerFeeByState::class;
    }
}
