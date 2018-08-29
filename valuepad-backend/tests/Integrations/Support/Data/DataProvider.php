<?php
namespace ValuePad\Tests\Integrations\Support\Data;

use ValuePad\Tests\Integrations\Support\Runtime\Runtime;

abstract class DataProvider
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var Runtime
	 */
	private $runtime;

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param array|callable $data
	 * @return $this
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		if (is_callable($this->data)){
			$this->data = call_user_func($this->data, $this->runtime);
		}

		return $this->data;
	}

	/**
	 * @param Runtime $runtime
	 * @return $this
	 */
	public function setRuntime(Runtime $runtime)
	{
		$this->runtime = $runtime;
		return $this;
	}

	/**
	 * @return Runtime
	 */
	public function getRuntime()
	{
		return $this->runtime;
	}

	/**
	 * @param $path
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($path, $default = null)
	{
		return array_get($this->getData(), $path, $default);
	}
}
