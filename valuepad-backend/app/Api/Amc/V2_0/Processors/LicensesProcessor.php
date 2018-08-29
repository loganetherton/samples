<?php
namespace ValuePad\Api\Amc\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Amc\Persistables\CoveragePersistable;
use ValuePad\Core\Amc\Persistables\LicensePersistable;

class LicensesProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'state' => 'string',
            'number' => 'string',
            'expiresAt' => 'datetime',
            'document' => 'document',
            'coverage' => [
                'county' => 'int',
                'zips' => 'string[]'
            ],
            'alias' => 'array',
            'alias.companyName' => 'string',
            'alias.address1' => 'string',
            'alias.address2' => 'string',
            'alias.city' => 'string',
            'alias.state' => 'string',
            'alias.zip' => 'string',
            'alias.phone' => 'string',
            'alias.fax' => 'string',
            'alias.email' => 'string',
        ];
    }

    /**
     * @return LicensePersistable
     */
    public function createPersistable()
    {
        return $this->populate(new LicensePersistable(), [
            'map' => [
                'coverage' => 'coverages'
            ],
            'hint' => [
                'coverage' => 'collection:'.CoveragePersistable::class
            ]
        ]);
    }
}
