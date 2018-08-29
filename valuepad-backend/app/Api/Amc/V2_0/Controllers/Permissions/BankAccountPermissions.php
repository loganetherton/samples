<?php
namespace ValuePad\Api\Amc\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class BankAccountPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'change' => 'owner',
            'show' => 'owner'
        ];
    }
}
