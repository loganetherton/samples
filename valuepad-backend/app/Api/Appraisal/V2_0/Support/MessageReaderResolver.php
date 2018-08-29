<?php
namespace ValuePad\Api\Appraisal\V2_0\Support;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;

class MessageReaderResolver
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * @param Session $session
     * @param EnvironmentInterface $environment
     */
    public function __construct(Session $session, EnvironmentInterface $environment)
    {
        $this->session = $session;
        $this->environment = $environment;
    }

    public function getReader()
    {
        $user = $this->session->getUser();

        if (!$user instanceof Customer){
            return $user->getId();
        }

        if ($assignee = $this->environment->getAssigneeAsWhoActorActs()){
            return $assignee;
        }

        return $user->getId();
    }
}
