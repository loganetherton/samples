<?php
namespace ValuePad\Api\Assignee\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Assignee\Entities\CustomerFee;

class CustomerFeeTransformer extends BaseTransformer
{
    /**
     * @param CustomerFee $fee
     * @return array
     */
    public function transform($fee)
    {
        return $this->extract($fee);
    }
}
