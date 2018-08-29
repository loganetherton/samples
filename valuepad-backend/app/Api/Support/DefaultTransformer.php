<?php
namespace ValuePad\Api\Support;

class DefaultTransformer extends BaseTransformer
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
