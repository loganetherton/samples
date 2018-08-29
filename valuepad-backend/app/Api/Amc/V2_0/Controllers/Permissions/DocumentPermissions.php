<?php
namespace ValuePad\Api\Amc\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class DocumentPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'formats' => 'owner',
            'store' => 'owner',
            'update' => 'owner',
            'show' => 'owner'
        ];
    }
}
