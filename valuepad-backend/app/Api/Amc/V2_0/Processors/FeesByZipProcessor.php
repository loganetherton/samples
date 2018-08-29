<?php
namespace ValuePad\Api\Amc\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Api\Support\BulkHolder;
use ValuePad\Core\Amc\Persistables\FeeByZipPersistable;

class FeesByZipProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'data' => [
                'zip' => 'string',
                'amount' => 'float'
            ]
        ];
    }

    /**
     * @return FeeByZipPersistable[]
     */
    public function createPersistables()
    {
        $holder = new BulkHolder();

        $this->populate($holder, [
            'hint' => [
                'data' => 'collection:'.FeeByZipPersistable::class
            ]
        ]);

        return $holder->getData();
    }
}
