<?php
namespace ValuePad\Core\Company\Interfaces;

interface DistanceCalculatorInterface
{
    /**
     * Given a collection of zips, calculate the distance between each point of origin
     * against the destination. Returns an array of integers in which each key
     * is the origin and the value is the distance between the origin and the destination.
     *
     * @param string[] $origins
     * @param string $destination
     * @return array
     */
    public function calculate(array $origins = [], $destination);
}
