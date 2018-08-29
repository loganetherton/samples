<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Company\V2_0\Protectors\AdminForManagerProtector;

class ManagersPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'update' => ['owner', AdminForManagerProtector::class],
            'show' => ['owner', AdminForManagerProtector::class],
            'updateAvailability' => 'owner'
        ];
    }
}
