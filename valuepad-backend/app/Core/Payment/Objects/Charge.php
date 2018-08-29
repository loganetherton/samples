<?php
namespace ValuePad\Core\Payment\Objects;

use ValuePad\Core\Payment\Enums\Status;

class Charge
{
	/**
	 * @var string
	 */
	private $transactionId;

	/**
	 * @var Status
	 */
	private $status;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @return string
	 */
	public function getTransactionId()
	{
		return $this->transactionId;
	}

	/**
	 * @param string $transactionId
	 */
	public function setTransactionId($transactionId)
	{
		$this->transactionId = $transactionId;
	}

	/**
	 * @return Status
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param Status $status
	 */
	public function setStatus(Status $status)
	{
		$this->status = $status;
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}
}
