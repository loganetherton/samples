<?php
namespace ValuePad\Core\Amc\Services;

use ValuePad\Core\Amc\Validation\FeeByStateValidator;

class FeeByStateService extends AbstractFeeByStateService
{
    use UseFeeByStateTrait;
    use UseFeeTrait;

    /**
     * @return FeeByStateValidator
     */
    protected function getValidator()
    {
        return new FeeByStateValidator($this->container);
    }
}
