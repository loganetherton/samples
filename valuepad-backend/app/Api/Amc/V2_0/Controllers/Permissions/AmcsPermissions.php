<?php
namespace ValuePad\Api\Amc\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Amc\V2_0\Protectors\CustomerProtector;

class AmcsPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => 'admin',
            'store' => 'all',
            'show' => ['owner', 'admin', CustomerProtector::class],
            'update' => ['owner', 'admin']
        ];
    }
}
