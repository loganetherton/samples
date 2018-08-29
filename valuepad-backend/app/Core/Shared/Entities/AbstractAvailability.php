<?php
namespace ValuePad\Core\Shared\Entities;

use DateTime;

abstract class AbstractAvailability
{
    /**
     * @var int
     */
    protected $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var bool
     */
    protected $isOnVacation;
    public function setOnVacation($flag) { $this->isOnVacation = $flag; }
    public function isOnVacation() { return $this->isOnVacation; }

    /**
     * @var DateTime
     */
    protected $from;
    public function getFrom() { return $this->from; }
    public function setFrom(DateTime $from = null) { $this->from = $from; }

    /**
     * @var DateTime
     */
    protected $to;
    public function getTo() { return $this->to; }
    public function setTo(DateTime $to = null) { $this->to = $to; }

    /**
     * @var string
     */
    protected $message;
    public function setMessage($message) { $this->message = $message; }
    public function getMessage() { return $this->message; }

    public function __construct()
    {
        $this->setOnVacation(false);
    }
}
