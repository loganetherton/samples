<?php
namespace ValuePad\Core\Customer\Persistables;

use ValuePad\Core\Appraisal\Persistables\MessagePersistable as BaseMessagePersistable;

class MessagePersistable extends BaseMessagePersistable
{
    /**
     * @var string
     */
    private $employee;
    public function setEmployee($employee) { $this->employee = $employee; }
    public function getEmployee() { return $this->employee; }
}
