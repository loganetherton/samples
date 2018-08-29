<?php
namespace ValuePad\Core\Log\Extras;

use ValuePad\Core\Appraisal\Entities\Property;

class LocationExtra extends Extra
{
	/**
	 * @param Property $property
	 * @return static
	 */
	public static function fromProperty(Property $property)
	{
		$extra = new static();

		$extra[Extra::ADDRESS_1] = $property->getAddress1();
		$extra[Extra::ADDRESS_2] = $property->getAddress2();
		$extra[Extra::CITY] = $property->getCity();
		$extra[Extra::ZIP] = $property->getZip();
		$extra[Extra::STATE] = StateExtra::fromState($property->getState());

		return $extra;
	}
}
