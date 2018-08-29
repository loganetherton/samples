<?php
namespace ValuePad\Api\Amc\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Api\Support\BulkHolder;
use ValuePad\Core\Amc\Persistables\FeeByCountyPersistable;

class FeesByCountyProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'data' => [
                'county' => 'int',
                'amount' => 'float',
            ]
        ];
    }

    /**
     * @return FeeByCountyPersistable[]
     */
    public function createPersistables()
    {
        $holder = new BulkHolder();

        $this->populate($holder, [
            'hint' => [
                'data' => 'collection:'.FeeByCountyPersistable::class
            ]
        ]);

        return $holder->getData();
    }
}
