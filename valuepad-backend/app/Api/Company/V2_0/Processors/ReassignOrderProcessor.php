<?php
namespace ValuePad\Api\Company\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;

class ReassignOrderProcessor extends BaseProcessor
{
    protected function configuration()
    {
        return [
            'appraiser' => 'int'
        ];
    }

    /**
     * @return int
     */
    public function getAppraiser()
    {
        return $this->get('appraiser');
    }
}
