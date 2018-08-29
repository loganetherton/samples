<?php
namespace ValuePad\Core\Location\Properties;

trait LongitudePropertyTrait
{
    /**
     * @var string
     */
    private $longitude;

    /**
     * @param string $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
}
