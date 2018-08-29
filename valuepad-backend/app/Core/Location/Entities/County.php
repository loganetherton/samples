<?php
namespace ValuePad\Core\Location\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use ValuePad\Core\Location\Properties\StatePropertyTrait;
use ValuePad\Core\Shared\Properties\IdPropertyTrait;
use ValuePad\Core\Shared\Properties\TitlePropertyTrait;

/**
 *
 *
 */
class County
{
    use IdPropertyTrait;
	use TitlePropertyTrait;
    use StatePropertyTrait;

	/**
	 * @var Zip[]
	 */
	private $zips;

	public function __construct()
	{
		$this->zips = new ArrayCollection();
	}

	/**
	 * @return ArrayCollection|Zip[]
	 */
	public function getZips()
	{
		return $this->zips;
	}

	/**
	 * @param Zip $zip
	 */
	public function addZip(Zip $zip)
	{
		$this->zips->add($zip);
	}
}
