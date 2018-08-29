<?php
namespace ValuePad\Core\Location\Properties;

trait LatitudePropertyTrait
{
    /**
     * @var string
     */
    private $latitude;

    /**
     * @param string $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
}
