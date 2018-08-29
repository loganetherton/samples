<?php
namespace ValuePad\Core\Appraiser\Entities;

use ValuePad\Core\Assignee\Interfaces\CoverageInterface;
use ValuePad\Core\Assignee\Interfaces\CoverageStorableInterface;
use ValuePad\Core\Location\Entities\County;

class Coverage implements CoverageInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var County
     */
    private $county;
    public function setCounty(County $county) { $this->county = $county; }
    public function getCounty() { return $this->county; }

    /**
     * @var string
     */
    private $zip;
    public function setZip($zip) { $this->zip = $zip; }
    public function getZip() { return $this->zip; }

	/**
	 * @var License|CoverageStorableInterface
	 */
	private $license;

    /**
     * @param License|CoverageStorableInterface $license
     */
    public function setLicense(CoverageStorableInterface $license) { $this->license = $license; }
}
