<?php
namespace ValuePad\Core\Appraisal\Emails;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Support\Letter\Email;

class UnacceptedReminderEmail extends Email
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
