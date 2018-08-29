<?php
namespace ValuePad\Api\Asc\V2_0\Processors;

use ValuePad\Api\Support\Searchable\BaseSearchableProcessor;
use ValuePad\Core\Support\Criteria\Constraint;

class AppraisersSearchableProcessor extends BaseSearchableProcessor
{
    protected function configuration()
    {
        return [
            'search' => [
                'licenseNumber' => Constraint::SIMILAR
            ],
            'filter' => [
                'licenseState' => Constraint::EQUAL,
				'isTied' => [
					'constraint' =>  Constraint::EQUAL,
					'type' => 'bool'
				]
            ]
        ];
    }
}
