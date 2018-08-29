<?php
namespace ValuePad\Core\Appraisal\Entities;
use DateTime;

class SupportingDetails
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }


    /**
     * @var Order
     */
    private $order;
    public function setOrder(Order $order) { $this->order = $order; }
    public function getOrder() { return $this->order; }

    /**
     * @var DateTime
     */
    private $unacceptedRemindedAt;
    public function setUnacceptedRemindedAt(DateTime $datetime) { $this->unacceptedRemindedAt = $datetime; }
    public function getUnacceptedRemindedAt() { return $this->unacceptedRemindedAt; }
}
