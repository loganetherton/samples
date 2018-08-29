<?php
namespace ValuePad\Core\Location\Properties;

use ValuePad\Core\Location\Entities\County;

trait CountyPropertyTrait
{
	/**
	 * @var County
	 */
	private $county;

	/**
	 * @param County $county
	 */
	public function setCounty(County $county)
	{
		$this->county = $county;
	}

	/**
	 * @return County
	 */
	public function getCounty()
	{
		return $this->county;
	}
}
