<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class CompanyInvitationsPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => 'owner',
            'accept' => 'owner',
            'decline' => 'owner'
        ];
    }
}
