<?php
namespace ValuePad\Api\Company\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;

class PermissionsProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'data' => 'int[]'
        ];
    }

    /**
     * @return array
     */
    public function getAppraiserStaffIds()
    {
        return $this->get('data', []);
    }
}
