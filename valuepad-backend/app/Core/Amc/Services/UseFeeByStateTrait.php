<?php
namespace ValuePad\Core\Amc\Services;

use ValuePad\Core\Amc\Entities\FeeByState;

trait UseFeeByStateTrait
{
    /**
     * @return string
     */
    protected function getFeeByStateClass()
    {
        return FeeByState::class;
    }
}
