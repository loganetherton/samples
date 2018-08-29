<?php
namespace ValuePad\Api\Amc\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Amc\Persistables\SettingsPersistable;

class SettingsProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'pushUrl' => 'string'
        ];
    }

    /**
     * @return SettingsPersistable
     */
    public function createPersistable()
    {
        return $this->populate(new SettingsPersistable());
    }
}
