<?php
namespace ValuePad\Tests\Integrations\Support;

use RuntimeException;

class Freezer
{
	/**
	 * @var Freezer[]
	 */
	private static $instances = [];

	/**
	 * @var array
	 */
	private $factories = [];

	/**
	 * @var array
	 */
	private $resolved = [];

	/**
	 * @param $source
	 * @return Freezer
	 */
	public static function getInstance($source)
	{
		if (!isset(self::$instances[$source])){
			self::$instances[$source] = new Freezer();
		}

		return self::$instances[$source];
	}

	/**
	 * @param string $name
	 * @param callable $factory
	 */
	public function register($name, callable $factory)
	{
		if (isset($this->factories[$name])){
			throw new RuntimeException('The "'.$name.'" field is registered already.');
		}

		$this->factories[$name] = $factory;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function get($name)
	{
		if (!array_key_exists($name, $this->resolved)){

			if (!isset($this->factories[$name])){
				throw new RuntimeException('Unable to get a factory for the "'.$name.'" field.');
			}

			$args = func_get_args();
			array_shift($args);
			$this->resolved[$name] = call_user_func_array($this->factories[$name], $args);
		}

		return $this->resolved[$name];
	}

	public function reset()
	{
		$this->resolved = [];
		$this->factories = [];
	}
}
