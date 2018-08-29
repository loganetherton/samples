<?php
namespace ValuePad\Api\Amc\V2_0\Processors;
use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Api\Support\BulkHolder;
use ValuePad\Core\Assignee\Persistables\FeePersistable;

class FeesProcessor extends BaseProcessor
{
    /**
     * @return array
     */
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
