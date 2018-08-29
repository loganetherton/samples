<?php
namespace ValuePad\Core\Location\Properties;

trait ZipPropertyTrait
{
    /**
     * @var string
     */
    private $zip;

	/**
	 * @param string $zip
	 */
	public function setZip($zip)
	{
		$this->zip = $zip;
	}

	/**
	 * @return string
	 */
	public function getZip()
	{
		return $this->zip;
	}
}
