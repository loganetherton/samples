<?php
namespace ValuePad\Core\Shared\Properties;

use DateTime;

trait CreatedAtPropertyTrait
{
	/**
	 * @var DateTime
	 */
	private $createdAt;

	/**
	 * @param DateTime $datetime
	 */
	public function setCreatedAt(DateTime $datetime)
	{
		$this->createdAt = $datetime;
	}

	/**
	 * @return DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}
}
