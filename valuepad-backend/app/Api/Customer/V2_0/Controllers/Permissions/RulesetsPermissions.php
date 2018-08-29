<?php
namespace ValuePad\Api\Customer\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class RulesetsPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'show' => 'owner',
            'store' => 'owner',
            'destroy' => 'owner',
            'update' => 'owner'
        ];
    }
}
