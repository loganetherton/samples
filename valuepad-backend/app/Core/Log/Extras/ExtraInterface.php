<?php
namespace ValuePad\Core\Log\Extras;

interface ExtraInterface
{
	/**
	 * @param array $data
	 */
	public function setData(array $data);

	/**
	 * @return array
	 */
	public function getData();
}
