<?php
namespace ValuePad\Core\Appraiser\Persistables;
use ValuePad\Core\Assignee\Interfaces\CoveragePersistableInterface;

class CoveragePersistable implements CoveragePersistableInterface
{
    /**
     * @var int
     */
    private $county;
    public function setCounty($county) { $this->county = $county; }
    public function getCounty() { return $this->county; }

    /**
     * @var array
     */
    private $zips;
    public function setZips(array $zips) { $this->zips = $zips; }
    public function getZips() { return $this->zips; }
}
