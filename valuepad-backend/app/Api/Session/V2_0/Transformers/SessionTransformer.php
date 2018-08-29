<?php
namespace ValuePad\Api\Session\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Session\Entities\Session;

class SessionTransformer extends BaseTransformer
{
    /**
     * @param Session $session
     * @return array
     */
    public function transform($session)
    {
        return $this->extract($session);
    }
}
