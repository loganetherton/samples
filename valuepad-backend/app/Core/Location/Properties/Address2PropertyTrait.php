<?php
namespace ValuePad\Core\Location\Properties;

/**
 *
 *
 */
trait Address2PropertyTrait
{
    /**
     * @var string
     */
    private $address2;

	/**
	 * @param string $address
	 */
	public function setAddress2($address)
	{
		$this->address2 = $address;
	}

	/**
	 * @return string
	 */
	public function getAddress2()
	{
		return $this->address2;
	}
}
