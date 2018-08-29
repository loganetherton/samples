<?php
namespace ValuePad\Api\Amc\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class AdditionalDocumentsPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => 'owner',
            'store' => 'owner',
            'types' => 'owner'
        ];
    }
}
