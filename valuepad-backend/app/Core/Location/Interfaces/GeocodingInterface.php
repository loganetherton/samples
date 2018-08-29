<?php
namespace ValuePad\Core\Location\Interfaces;
use ValuePad\Core\Location\Objects\Coordinates;
use ValuePad\Core\Location\Objects\Location;

interface GeocodingInterface
{
    /**
     * @param Location $location
     * @return Coordinates
     */
    public function toCoordinates(Location $location);

    /**
     * @param Coordinates $coordinates
     * @return Location
     */
    public function toLocation(Coordinates $coordinates);
}
