<?php
namespace ValuePad\Api\Company\V2_0\Processors;
use ValuePad\Api\Support\Searchable\BaseSearchableProcessor;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\Support\Criteria\Constraint;
use ValuePad\Core\Support\Criteria\Criteria;

class StaffSearchableProcessor extends BaseSearchableProcessor
{
    protected function configuration()
    {
        return [
            'filter' => [
                'user.type' => function($value){
                    if (!in_array($value, ['appraiser', 'manager'], true)){
                        return null;
                    }

                    $constraint = new Constraint(Constraint::EQUAL);

                    $class = $value === 'manager' ? Manager::class : Appraiser::class;

                    return new Criteria('user.class', $constraint, $class);
                },
            ]
        ];
    }
}
