<?php
namespace ValuePad\Core\Customer\Entities;

use ValuePad\Core\Appraisal\Entities\Message as BaseMessage;

class Message extends BaseMessage
{
    /**
     * @var string
     */
    private $employee;
    public function setEmployee($employee) { $this->employee = $employee; }
    public function getEmployee() { return $this->employee; }
}
