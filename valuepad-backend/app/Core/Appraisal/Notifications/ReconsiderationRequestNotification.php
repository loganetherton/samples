<?php
namespace ValuePad\Core\Appraisal\Notifications;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Appraisal\Entities\Reconsideration;

class ReconsiderationRequestNotification extends AbstractNotification implements UpdateProcessStatusNotificationAwareInterface
{
    use UpdateProcessStatusNotificationAwareTrait;

    /**
     * @var Reconsideration
     */
    private $reconsideration;

    /**
     * @param Order $order
     * @param Reconsideration $reconsideration
     */
    public function __construct(Order $order, Reconsideration $reconsideration)
    {
        parent::__construct($order);

        $this->reconsideration = $reconsideration;
    }

    /**
     * @return Reconsideration
     */
    public function getReconsideration()
    {
        return $this->reconsideration;
    }
}
