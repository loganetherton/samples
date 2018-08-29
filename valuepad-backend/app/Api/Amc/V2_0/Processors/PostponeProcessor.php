<?php
namespace ValuePad\Api\Amc\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;

class PostponeProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'explanation' => 'string',
        ];
    }

    /**
     * @return string
     */
    public function getExplanation()
    {
        return $this->get('explanation');
    }
}
