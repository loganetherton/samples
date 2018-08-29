<?php
namespace ValuePad\Core\Assignee\Interfaces;

interface CoveragePersistableInterface
{
    /**
     * @param int $county
     */
    public function setCounty($county);

    /**
     * @return int
     */
    public function getCounty();

    /**
     * @param array $zips
     */
    public function setZips(array $zips);

    /**
     * @return array $zips
     */
    public function getZips();
}
