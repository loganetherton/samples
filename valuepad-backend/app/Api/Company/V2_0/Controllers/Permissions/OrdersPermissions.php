<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Company\V2_0\Protectors\CompanyManagerProtector;

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
            'listAdditionalStatuses' => 'owner',
            'changeAdditionalStatus' => 'owner',
            'reassign' => 'owner',
            'totals' => 'owner',
            'accounting' => 'owner',
        ];
    }
}
