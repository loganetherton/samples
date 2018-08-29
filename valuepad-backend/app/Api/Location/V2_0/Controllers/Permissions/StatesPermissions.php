<?php
namespace ValuePad\Api\Location\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

/**
 *
 *
 */
class StatesPermissions extends AbstractActionsPermissions
{

    /**
     *
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => 'all',
            'zips' => 'all'
        ];
    }
}
