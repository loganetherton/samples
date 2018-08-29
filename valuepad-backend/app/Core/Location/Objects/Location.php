<?php
namespace ValuePad\Core\Location\Objects;
use ValuePad\Core\Location\Properties\Address1PropertyTrait;
use ValuePad\Core\Location\Properties\Address2PropertyTrait;
use ValuePad\Core\Location\Properties\CityPropertyTrait;
use ValuePad\Core\Location\Properties\StateAsCodePropertyTrait;
use ValuePad\Core\Location\Properties\ZipPropertyTrait;

class Location
{
    use Address1PropertyTrait;
    use Address2PropertyTrait;
    use CityPropertyTrait;
    use StateAsCodePropertyTrait;
    use ZipPropertyTrait;

    public function __toString()
    {
        return $this->getAddress1().', '.$this->getCity().', '.$this->getState().' '.$this->getZip();
    }
}
