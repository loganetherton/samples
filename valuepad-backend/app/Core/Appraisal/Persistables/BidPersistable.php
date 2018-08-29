<?php
namespace ValuePad\Core\Appraisal\Persistables;

use DateTime;

class BidPersistable
{
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
	 * @var int[]
	 */
	private $appraisers;
	public function setAppraisers(array $appraisers) { $this->appraisers = $appraisers; }
	public function getAppraisers() { return $this->appraisers; }
}
