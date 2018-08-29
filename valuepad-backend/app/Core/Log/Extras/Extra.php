<?php
namespace ValuePad\Core\Log\Extras;

use ArrayAccess;
class Extra implements ExtraInterface, ArrayAccess
{
	const USER = 'user';
	const CUSTOMER = 'customer';
	const TYPE = 'type';
	const NAME = 'name';
	const FORMAT = 'format';
	const URL = 'url';
	const SIZE = 'size';
	const OLD_ADDITIONAL_STATUS = 'oldAdditionalStatus';
	const OLD_ADDITIONAL_STATUS_COMMENT = 'oldAdditionalStatusComment';
	const NEW_ADDITIONAL_STATUS = 'newAdditionalStatus';
	const NEW_ADDITIONAL_STATUS_COMMENT = 'newAdditionalStatusComment';
	const OLD_PROCESS_STATUS = 'oldProcessStatus';
	const NEW_PROCESS_STATUS = 'newProcessStatus';
	const EXPLANATION = 'explanation';
	const ESTIMATED_COMPLETION_DATE = 'estimatedCompletionDate';
	const SCHEDULED_AT = 'scheduledAt';
	const COMPLETED_AT = 'completedAt';
	const ADDRESS_1 = 'address1';
	const ADDRESS_2 = 'address2';
	const CITY = 'city';
	const ZIP = 'zip';
	const STATE = 'state';
	const TITLE = 'title';
	const COMMENT = 'comment';
	const CODE = 'code';

	/**
	 * @var array
	 */
	private $data = [];

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param array $data
	 */
	public function setData(array $data)
	{
		$this->data = $data;
	}

	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	/**
	 * @param string $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	/**
	 * @param string $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		if ($value instanceof ExtraInterface){
			$value = $value->getData();
		}

		$this->data[$offset] = $value;
	}

	/**
	 * @param string $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	/**
	 * @param ExtraInterface $extra
	 */
	public function merge(ExtraInterface $extra)
	{
		$this->data = array_merge($this->data, $extra->getData());
	}
}
