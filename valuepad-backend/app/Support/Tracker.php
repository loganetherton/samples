<?php
namespace ValuePad\Support;

use IteratorIterator;
use Traversable;

class Tracker extends IteratorIterator
{
	/**
	 * @var int
	 */
	private $tellAt;

	/**
	 * @var int
	 */
	private $counter = 0;

	/**
	 * @param Traversable $iterator
	 * @param int $tellAt
	 */
	public function __construct(Traversable $iterator, $tellAt)
	{
		parent::__construct($iterator);

		$this->tellAt = $tellAt;
	}

	public function current()
	{
		$this->counter ++;
		return parent::current();
	}

	/**
	 * @return int
	 */
	public function isTime()
	{
		return ($this->counter % $this->tellAt) === 0;
	}

	public function rewind()
	{
		parent::rewind();
		$this->counter = 0;
	}
}
