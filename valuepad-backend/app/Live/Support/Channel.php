<?php
namespace ValuePad\Live\Support;
use ValuePad\Core\User\Entities\User;

class Channel
{
    /**
     * @var User
     */
    private $receiver;

    /**
     * @var User
     */
    private $actsAs;

    /**
     * @param User $receiver
     * @param User $actsAs
     */
    public function __construct($receiver, $actsAs = null)
    {
        $this->receiver = $receiver;
        $this->actsAs = $actsAs;
    }

    /**
     * @return User
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @return User
     */
    public function getActsAs()
    {
        return $this->actsAs;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $channel = 'private-user-'.$this->receiver->getId();

        if ($this->actsAs !== null){
            $channel .= '-as-'.$this->actsAs->getId();
        }

        return $channel;
    }
}
