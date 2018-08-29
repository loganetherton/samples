<?php
namespace ValuePad\Api\Asc\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Asc\Entities\AscAppraiser;

class AppraiserTransformer extends BaseTransformer
{

    /**
     * @param AscAppraiser $appraiser
     * @return array
     */
    public function transform($appraiser)
    {
        return $this->extract($appraiser);
    }
}
