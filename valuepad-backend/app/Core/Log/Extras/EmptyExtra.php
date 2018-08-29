<?php
namespace ValuePad\Core\Log\Extras;

class EmptyExtra implements ExtraInterface
{
	/**
	 * @param array $data
	 */
	public function setData(array $data)
	{
		//
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return [];
	}
}
