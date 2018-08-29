<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class AppraisersPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'store' => 'all',
            'show' => 'auth',
			'index' => 'auth',
            'update' => ['owner', 'admin'],
			'changePrimaryLicense' => 'owner',
			'updateAvailability' => 'owner'
        ];
    }
}
