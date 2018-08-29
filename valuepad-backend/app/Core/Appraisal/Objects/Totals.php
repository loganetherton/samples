<?php
namespace ValuePad\Core\Appraisal\Objects;

class Totals
{
	/**
	 * @var int
	 */
	private $total;
	public function getTotal() { return $this->total; }
	public function setTotal($total) { $this->total = $total; }

	/**
	 * @var float
	 */
	private $fee;
	public function getFee() { return $this->fee; }
	public function setFee($fee) { $this->fee = $fee; }

	/**
	 * @var float
	 */
	private $techFee;
	public function getTechFee() { return $this->techFee; }
	public function setTechFee($fee) { $this->techFee = $fee; }
}
