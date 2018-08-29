<?php
namespace ValuePad\Api\Company\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Api\Support\BulkHolder;
use ValuePad\Core\Company\Persistables\FeePersistable;

class FeesProcessor extends BaseProcessor
{
    protected function configuration()
    {
        return [
            'data' => [
                'jobType' => 'int',
                'amount' => 'float'
            ]
        ];
    }

    /**
     * @return FeePersistable[]
     */
    public function createPersistables()
    {
        $holder = new BulkHolder();

        $this->populate($holder, [
            'hint' => [
                'data' => 'collection:'.FeePersistable::class
            ]
        ]);

        return $holder->getData();
    }
}
