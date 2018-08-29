<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class AvailabilityPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'show' => 'owner',
            'replace' => 'owner'
        ];
    }
}
