<?php
namespace ValuePad\Core\Location\Properties;

trait AddressPropertyTrait
{
	/**
	 * @var string
	 */
	private $address;

	/**
	 * @param string $address
	 */
	public function setAddress($address)
	{
		$this->address = $address;
	}

	/**
	 * @return string
	 */
	public function getAddress()
	{
		return $this->address;
	}
}
