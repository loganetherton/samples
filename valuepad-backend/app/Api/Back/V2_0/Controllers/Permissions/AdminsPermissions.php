<?php
namespace ValuePad\Api\Back\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class AdminsPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'store' => 'admin',
            'show' => 'owner',
            'update' => 'owner'
        ];
    }
}
