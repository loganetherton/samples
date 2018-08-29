<?php
namespace ValuePad\Api\Customer\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class AppraiserQueuesPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => 'owner',
            'counters' => 'owner'
        ];
    }
}
