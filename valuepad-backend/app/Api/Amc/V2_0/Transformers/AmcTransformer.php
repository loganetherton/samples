<?php
namespace ValuePad\Api\Amc\V2_0\Transformers;
use ValuePad\Api\Support\BaseTransformer;

class AmcTransformer extends BaseTransformer
{
    /**
     * @param object $item
     * @return array
     */
    public function transform($item)
    {
        return $this->extract($item);
    }
}
