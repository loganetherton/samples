<?php
namespace ValuePad\Mobile\Support;

class News
{
	/**
	 * @var string
	 */
	private $message;

	/**
	 * @var array
	 */
	private $extra = [];

	/**
	 * @var string
	 */
	private $category;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param string $message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}

	/**
	 * @return array
	 */
	public function getExtra()
	{
		return $this->extra;
	}

	/**
	 * @param array $extra
	 */
	public function setExtra($extra)
	{
		$this->extra = $extra;
	}

	/**
	 * @return string
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param string $category
	 */
	public function setCategory($category)
	{
		$this->category = $category;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}
}
