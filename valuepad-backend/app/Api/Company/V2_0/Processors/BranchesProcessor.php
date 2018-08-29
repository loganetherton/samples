<?php
namespace ValuePad\Api\Company\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Company\Persistables\BranchPersistable;

class BranchesProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'name' => 'string',
            'taxId' => 'string',
            'address1' => 'string',
            'address2' => 'string',
            'city' => 'string',
            'state' => 'string',
            'zip' => 'string',
            'assignmentZip' => 'string',
            'eo.document' => 'document',
            'eo.claimAmount' => 'float',
            'eo.aggregateAmount' => 'float',
            'eo.expiresAt' => 'datetime',
            'eo.carrier' => 'string',
            'eo.deductible' => 'float',
        ];
    }

    /**
     * @return BranchPersistable
     */
    public function createPersistable()
    {
        return $this->populate(new BranchPersistable());
    }
}
