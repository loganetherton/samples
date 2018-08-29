<?php
namespace ValuePad\Core\Amc\Services;

use ValuePad\Core\Amc\Entities\Fee;

trait UseFeeTrait
{
    /**
     * @return string
     */
    protected function getFeeClass()
    {
        return Fee::class;
    }
}
