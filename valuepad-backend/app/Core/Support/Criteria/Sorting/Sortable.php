<?php
namespace ValuePad\Core\Support\Criteria\Sorting;

class Sortable
{
	/**
	 * @var string
	 */
	private $property;

	/**
	 * @var Direction
	 */
	private $direction;

	/**
	 * @param string $property
	 * @param Direction $direction
	 */
	public function __construct($property, Direction $direction = null)
	{
		if ($direction === null){
			$direction = new Direction(Direction::ASC);
		}

		$this->property = $property;
		$this->direction = $direction;
	}

	/**
	 * @return string
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * @return Direction
	 */
	public function getDirection()
	{
		return $this->direction;
	}
}
