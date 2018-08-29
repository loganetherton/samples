<?php
namespace ValuePad\Api\Amc\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Amc\V2_0\Protectors\CustomerProtector;

class CustomerFeesPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => ['owner', CustomerProtector::class],
            'store' => 'owner',
            'update' => 'owner',
            'updateBulk' => 'owner',
            'destroy' => 'owner',
            'destroyBulk' => 'owner',
            'applyDefaultLocationFees' => 'owner',
            'showFeeByZip' => ['owner', CustomerProtector::class]
        ];
    }
}
