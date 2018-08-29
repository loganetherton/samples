<?php
namespace ValuePad\Api\Company\V2_0\Processors;

use ValuePad\Api\Support\Searchable\BaseSearchableProcessor;

class AppraisersSearchableProcessor extends BaseSearchableProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'orderId' => 'int',
            'distance' => 'int'
        ];
    }

    public function getOrderId()
    {
        return $this->get('orderId');
    }

    public function getDistance()
    {
        return $this->get('distance');
    }
}
