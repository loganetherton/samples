<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class InspectionPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'schedule' => 'owner',
            'complete' => 'owner'
        ];
    }
}
