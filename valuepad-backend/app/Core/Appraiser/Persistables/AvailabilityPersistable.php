<?php
namespace ValuePad\Core\Appraiser\Persistables;

use DateTime;

class AvailabilityPersistable
{
	/**
	 * @var bool
	 */
	private $isOnVacation;
	public function setOnVacation($flag) { $this->isOnVacation = $flag; }
	public function isOnVacation() { return $this->isOnVacation; }

	/**
	 * @var DateTime
	 */
	private $from;
	public function getFrom() { return $this->from; }
	public function setFrom(DateTime $from = null) { $this->from = $from; }

	/**
	 * @var DateTime
	 */
	private $to;
	public function getTo() { return $this->to; }
	public function setTo(DateTime $to = null) { $this->to = $to; }

	/**
	 * @var string
	 */
	private $message;
	public function setMessage($message) { $this->message = $message; }
	public function getMessage() { return $this->message; }

}
