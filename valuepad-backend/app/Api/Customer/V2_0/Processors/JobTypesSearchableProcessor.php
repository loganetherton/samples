<?php
namespace ValuePad\Api\Customer\V2_0\Processors;
use ValuePad\Api\Support\Searchable\BaseSearchableProcessor;
use ValuePad\Core\Support\Criteria\Constraint;

class JobTypesSearchableProcessor extends BaseSearchableProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'filter' => [
                'isPayable' => [
                    'constraint' => Constraint::EQUAL,
                    'type' => 'bool'
                ]
            ]
        ];
    }
}
