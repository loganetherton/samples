<?php
namespace ValuePad\Core\Location\Properties;

trait CityPropertyTrait
{
    /**
     * @var string
     */
    private $city;

	/**
	 * @param string $city
	 */
	public function setCity($city)
	{
		$this->city = $city;
	}

	/**
	 * @return string
	 */
	public function getCity()
	{
		return $this->city;
	}
}
