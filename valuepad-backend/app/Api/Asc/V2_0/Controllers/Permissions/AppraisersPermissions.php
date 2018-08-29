<?php
namespace ValuePad\Api\Asc\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

/**
 *
 *
 */
class AppraisersPermissions extends AbstractActionsPermissions
{

    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => 'all'
        ];
    }
}
