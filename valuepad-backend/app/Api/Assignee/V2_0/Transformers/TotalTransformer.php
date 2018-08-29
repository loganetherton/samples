<?php
namespace ValuePad\Api\Assignee\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Assignee\Objects\Total;

class TotalTransformer extends BaseTransformer
{
    /**
     * @param Total $total
     * @return array
     */
    public function transform($total)
    {
        return $this->extract($total);
    }
}
