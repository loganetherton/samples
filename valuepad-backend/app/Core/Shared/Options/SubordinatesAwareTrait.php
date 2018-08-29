<?php
namespace ValuePad\Core\Shared\Options;

trait SubordinatesAwareTrait
{
    /**
     * @var bool
     */
    private $withSubordinates = true;

    /**
     * @param bool $withSubordinates
     */
    public function setWithSubordinates($withSubordinates = true)
    {
        $this->withSubordinates = (bool) $withSubordinates;
    }

    /**
     * @return bool
     */
    public function getWithSubordinates()
    {
        return $this->withSubordinates;
    }
}
