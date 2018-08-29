<?php
namespace ValuePad\Api\Amc\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class BidPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'store' => 'owner',
            'show' => 'owner'
        ];
    }
}
