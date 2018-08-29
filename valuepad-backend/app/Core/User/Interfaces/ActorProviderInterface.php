<?php
namespace ValuePad\Core\User\Interfaces;
use ValuePad\Core\User\Entities\User;

interface ActorProviderInterface
{
    /**
     * @return User
     */
    public function getActor();
}
