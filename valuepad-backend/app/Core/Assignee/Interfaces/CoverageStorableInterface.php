<?php
namespace ValuePad\Core\Assignee\Interfaces;

interface CoverageStorableInterface
{
    /**
     * @param CoverageInterface $coverage
     */
    public function addCoverage(CoverageInterface $coverage);

    /**
     * @return CoverageInterface[]
     */
    public function getCoverages();
    public function clearCoverages();
}
