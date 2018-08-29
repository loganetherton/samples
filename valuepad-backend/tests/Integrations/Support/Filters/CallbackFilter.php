<?php
namespace ValuePad\Tests\Integrations\Support\Filters;

use Ascope\QA\Support\Filters\FilterInterface;

class CallbackFilter implements FilterInterface
{
	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * @param callable $callback
	 */
	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function filter(array $data)
	{
		return call_user_func($this->callback, $data);
	}
}
