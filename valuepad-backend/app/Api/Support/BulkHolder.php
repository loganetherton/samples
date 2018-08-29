<?php
namespace ValuePad\Api\Support;

class BulkHolder
{
	/**
	 * @var object[]
	 */
	private $bulk;

	/**
	 * @var object[]
	 */
	private $data;

	/**
	 * @return object[]
	 */
	public function getBulk()
	{
		return $this->bulk;
	}

	/**
	 * @param array $bulk
	 * @return object[]
	 */
	public function setBulk(array $bulk)
	{
		$this->bulk = $bulk;
	}

	/**
	 * @return object[]
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param array $data
	 * @return object[]
	 */
	public function setData(array $data)
	{
		$this->data = $data;
	}
}
