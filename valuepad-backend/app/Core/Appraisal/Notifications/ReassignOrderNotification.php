<?php
namespace ValuePad\Core\Appraisal\Notifications;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\User\Entities\User;

class ReassignOrderNotification extends AbstractNotification
{
    /**
     * @var User
     */
    private $oldAssignee;

    /**
     * @param Order $order
     * @param User $oldAssignee
     */
    public function __construct(Order $order, User $oldAssignee)
    {
        parent::__construct($order);

        $this->oldAssignee = $oldAssignee;
    }

    /**
     * @return User
     */
    public function getOldAssignee()
    {
        return $this->oldAssignee;
    }
}
