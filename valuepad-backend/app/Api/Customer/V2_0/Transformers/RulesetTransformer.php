<?php
namespace ValuePad\Api\Customer\V2_0\Transformers;
use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Customer\Entities\Ruleset;

class RulesetTransformer extends BaseTransformer
{
    /**
     * @param Ruleset $item
     * @return array
     */
    public function transform($item)
    {
        return $this->extract($item);
    }
}
