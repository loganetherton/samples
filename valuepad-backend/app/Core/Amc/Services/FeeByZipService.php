<?php
namespace ValuePad\Core\Amc\Services;

use ValuePad\Core\Amc\Entities\FeeByZip;

class FeeByZipService extends AbstractFeeByZipService
{
    use UseFeeByStateTrait;
    use UseFeeTrait;

    /**
     * @return string
     */
    protected function getFeeByZipClass()
    {
        return FeeByZip::class;
    }
}
