<?php
namespace ValuePad\Core\Support\Criteria;

use Ascope\Libraries\Enum\Enum;

class Constraint extends Enum
{
	const EQUAL = 'equal';
	const SIMILAR = 'similar';
	const GREATER_OR_EQUAL = 'greater-or-equal';
	const LESS_OR_EQUAL = 'less-or-equal';
	const IN = 'in';
	const CONTAIN = 'contain';

	private $isNot = false;

	/**
	 * @param mixed $value
	 * @param bool $isNot
	 */
	public function __construct($value, $isNot = false)
	{
		parent::__construct($value);
		$this->isNot = $isNot;
	}

	/**
	 * @param bool $flag
	 */
	public function setNot($flag)
	{
		$this->isNot = $flag;
	}

	/**
	 * @return bool
	 */
	public function isNot()
	{
		return $this->isNot;
	}
}
