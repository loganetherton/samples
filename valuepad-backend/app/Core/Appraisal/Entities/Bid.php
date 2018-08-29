<?php
namespace ValuePad\Core\Appraisal\Entities;

use DateTime;
use ValuePad\Core\Appraiser\Entities\Appraiser;

class Bid
{
	/**
	 * @var int
	 */
	private $id;
	public function setId($id) { $this->id = $id; }
	public function getId() { return $this->id; }

	/**
	 * @var float
	 */
	private $amount;
	public function getAmount() { return $this->amount; }
	public function setAmount($amount) { $this->amount = $amount; }

	/**
	 * @var DateTime
	 */
	private $estimatedCompletionDate;
	public function setEstimatedCompletionDate(DateTime $datetime = null) { $this->estimatedCompletionDate = $datetime; }
	public function getEstimatedCompletionDate() { return $this->estimatedCompletionDate; }

	/**
	 * @var string
	 */
	private $comments;
	public function setComments($comments) { $this->comments = $comments; }
	public function getComments() { return $this->comments; }

	/**
	 * @var Appraiser[]
	 */
	private $appraisers = [];
	public function setAppraisers($appraisers) { $this->appraisers = $appraisers; }
	public function getAppraisers() { return $this->appraisers; }

	/**
	 * @var Order
	 */
	private $order;
	public function getOrder() { return $this->order; }

	/**
	 * @param Order $order
	 */
	public function setOrder(Order $order)
	{
		$this->order = $order;
		$order->setBid($this);
	}
}
