<?php
namespace ValuePad\Api\Location\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Location\Entities\State;

/**
 *
 *
 */
class StateTransformer extends BaseTransformer
{

    /**
     *
     * @param State $state
     * @return array
     */
    public function transform($state)
    {
        return $this->extract($state);
    }
}
