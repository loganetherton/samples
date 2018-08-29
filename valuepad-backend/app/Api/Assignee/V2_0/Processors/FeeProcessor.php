<?php
namespace ValuePad\Api\Assignee\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Api\Support\BulkHolder;
use ValuePad\Core\Assignee\Persistables\FeePersistable;

class FeeProcessor extends BaseProcessor
{
    public function configuration()
    {
        $data = [
            'jobType' => 'int',
            'amount' => 'float'
        ];

        if ($this->isBulk()){
            $data = ['bulk' => $data];
        }

        return $data;
    }

    /**
     * @return FeePersistable
     */
    public function createPersistable()
    {
        return $this->populate(new FeePersistable());
    }

    /**
     * @return FeePersistable[]
     */
    public function createPersistables()
    {
        $holder = new BulkHolder();

        $this->populate($holder, [
            'hint' => [
                'bulk' => 'collection:'.FeePersistable::class
            ]
        ]);

        return $holder->getBulk();
    }

    /**
     * @return bool
     */
    public  function isBulk()
    {
        return array_key_exists('bulk', $this->toArray());
    }
}
