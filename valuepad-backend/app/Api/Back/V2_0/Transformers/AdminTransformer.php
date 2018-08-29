<?php
namespace ValuePad\Api\Back\V2_0\Transformers;
use ValuePad\Api\Support\BaseTransformer;

class AdminTransformer extends BaseTransformer
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
