<?php
namespace ValuePad\Api\Shared\Protectors;
use ValuePad\Core\Back\Entities\Admin;
use ValuePad\Core\Session\Entities\Session;

class AdminProtector extends AuthProtector
{
    public function grants()
    {
        if (! parent::grants()) {
            return false;
        }

        /**
         * @var Session $session
         */
        $session = $this->container->make(Session::class);

        return $session->getUser() instanceof Admin;
    }
}
