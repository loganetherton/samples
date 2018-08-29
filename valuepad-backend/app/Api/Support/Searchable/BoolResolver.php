<?php
namespace ValuePad\Api\Support\Searchable;

class BoolResolver
{
	/**
	 * @param string $value
	 * @return bool
	 */
	public function isProcessable($value)
	{
		return in_array($value, ['true', 'false'], true);
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	public function resolve($value)
	{
		return $value === 'true';
	}
}
