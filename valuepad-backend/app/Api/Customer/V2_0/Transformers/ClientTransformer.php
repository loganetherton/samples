<?php
namespace ValuePad\Api\Customer\V2_0\Transformers;
use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Customer\Entities\Client;

class ClientTransformer extends BaseTransformer
{
    /**
     * @param Client $item
     * @return array
     */
    public function transform($item)
    {
        return $this->extract($item);
    }
}
