<?php
namespace ValuePad\Core\Appraisal\Entities;

use DateTime;

class Revision
{
	/**
	 * @var int
	 */
	private $id;
	public function setId($id) { $this->id = $id; }
	public function getId() { return $this->id; }

	/**
	 * @var array
	 */
	private $checklist;
	public function setChecklist(array $checklist) { $this->checklist = $checklist; }
	public function getChecklist() { return $this->checklist; }

	/**
	 * @var string
	 */
	private $message;
	public function setMessage($message) { $this->message = $message; }
	public function getMessage() { return $this->message; }

	/**
	 * @var Order
	 */
	private $order;
	public function setOrder(Order $order) { $this->order = $order; }
	public function getOrder() { return $this->order; }
	/**
	 * @var DateTime
	 */
	private $createdAt;
	public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
	public function getCreatedAt() { return $this->createdAt; }

	public function __construct()
	{
		$this->setChecklist([]);
	}
}
