<?php
namespace ValuePad\Core\Amc\Services;

use ValuePad\Core\Amc\Entities\FeeByCounty;

class FeeByCountyService extends AbstractFeeByCountyService
{
    use UseFeeByStateTrait;
    use UseFeeTrait;

    /**
     * @return string
     */
    protected function getFeeByCountyClass()
    {
        return FeeByCounty::class;
    }
}
