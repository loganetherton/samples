<?php
namespace ValuePad\Api\Company\V2_0\Protectors;

use ValuePad\Api\Shared\Protectors\AuthProtector;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Session\Entities\Session;

class AppraiserProtector extends AuthProtector
{
    /**
     * @return bool
     */
    public function grants()
    {
        if (! parent::grants()) {
            return false;
        }

        $session = $this->container->make(Session::class);

        return $session->getUser() instanceof Appraiser;
    }
}
