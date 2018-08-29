<?php
namespace ValuePad\Core\Assignee\Interfaces;
use ValuePad\Core\Location\Entities\County;

interface CoverageInterface
{
    /**
     * @param County $county
     */
    public function setCounty(County $county);

    /**
     * @return County
     */
    public function getCounty();

    /**
     * @param string $zip
     */
    public function setZip($zip);

    /**
     * @return string
     */
    public function getZip();

    /**
     * @param CoverageStorableInterface $license
     */
    public function setLicense(CoverageStorableInterface $license);
}
