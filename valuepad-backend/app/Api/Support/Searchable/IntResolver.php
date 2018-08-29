<?php
namespace ValuePad\Api\Support\Searchable;

use Ascope\Libraries\Validation\Rules\IntegerCast;

class IntResolver
{
	/**
	 * @param string $value
	 * @return int
	 */
	public function isProcessable($value)
	{
		return !(new IntegerCast(true))->check($value);
	}

	/**
	 * @param string $value
	 * @return int
	 */
	public function resolve($value)
	{
		return (int) $value;
	}
}
