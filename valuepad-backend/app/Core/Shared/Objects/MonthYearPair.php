<?php
namespace ValuePad\Core\Shared\Objects;

class MonthYearPair
{
	/**
	 * @var int
	 */
	private $month;

	/**
	 * @var int
	 */
	private $year;

	/**
	 * @param int $month
	 * @param int $year
	 */
	public function __construct($month = null, $year = null)
	{
		$this->month = $month;
		$this->year = $year;
	}

	/**
	 * @return int
	 */
	public function getMonth()
	{
		return $this->month;
	}

	/**
	 * @param int $month
	 */
	public function setMonth($month)
	{
		$this->month = $month;
	}

	/**
	 * @return int
	 */
	public function getYear()
	{
		return $this->year;
	}

	/**
	 * @param int $year
	 */
	public function setYear($year)
	{
		$this->year = $year;
	}
}
