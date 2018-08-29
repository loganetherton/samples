<?php
namespace ValuePad\Core\Payment\Objects;

use ValuePad\Core\Shared\Objects\MonthYearPair;

class CreditCardRequisites extends AbstractRequisites
{
	/**
	 * @var string
	 */
	private $number;
    public function getNumber() { return $this->number; }
    public function setNumber($number) { $this->number = $number; }

	/**
	 * @var MonthYearPair
	 */
	private $expiresAt;
    public function getExpiresAt() { return $this->expiresAt; }
    public function setExpiresAt(MonthYearPair $expiresAt) { $this->expiresAt = $expiresAt; }

	/**
	 * @var string
	 */
	private $code;
	public function getCode() { return $this->code; }
	public function setCode($code) { $this->code = $code; }
}
