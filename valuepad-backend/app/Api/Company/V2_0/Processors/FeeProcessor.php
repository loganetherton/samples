<?php
namespace ValuePad\Api\Company\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Company\Persistables\FeePersistable;

class FeeProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'jobType' => 'int',
            'amount' => 'float'
        ];
    }

    /**
     * @return FeePersistable
     */
    public function createPersistable()
    {
        return $this->populate(new FeePersistable());
    }
}
