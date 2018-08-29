<?php
namespace ValuePad\Core\Location\Properties;

/**
 *
 *
 */
trait Address1PropertyTrait
{
    /**
     * @var string
     */
    private $address1;

	/**
	 * @param string $address
	 */
	public function setAddress1($address)
	{
		$this->address1 = $address;
	}

	/**
	 * @return string
	 */
	public function getAddress1()
	{
		return $this->address1;
	}
}
