<?php
namespace ValuePad\Api\Appraiser\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Appraiser\Persistables\LicensePersistable;
use ValuePad\Api\Appraiser\V2_0\Support\LicenseConfigurationTrait;

class LicensesProcessor extends BaseProcessor
{
    use LicenseConfigurationTrait;

    /**
     * @return array
     */
    protected function configuration()
    {
        return $this->getLicenseConfiguration();
    }

    /**
     * @return LicensePersistable
     */
    public function createPersistable()
    {
        return $this->populate(new LicensePersistable(), $this->getPopulatorConfig());
    }
}
