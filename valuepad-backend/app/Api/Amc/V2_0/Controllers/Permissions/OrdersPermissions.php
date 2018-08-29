<?php
namespace ValuePad\Api\Amc\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class OrdersPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => 'owner',
            'show' => 'owner',
            'accept' => 'owner',
            'acceptWithConditions' => 'owner',
            'decline' => 'owner',
            'totals' => 'owner',
            'changeAdditionalStatus' => 'owner',
            'listAdditionalStatuses' => 'owner',
            'destroy' => 'owner',
        ];
    }
}
