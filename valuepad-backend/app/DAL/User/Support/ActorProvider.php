<?php
namespace ValuePad\DAL\User\Support;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\User\Interfaces\ActorProviderInterface;

class ActorProvider implements ActorProviderInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }


    /**
     * @return User
     */
    public function getActor()
    {
        return $this->session->getUser();
    }
}
