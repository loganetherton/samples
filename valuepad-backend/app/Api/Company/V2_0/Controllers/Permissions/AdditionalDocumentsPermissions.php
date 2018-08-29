<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class AdditionalDocumentsPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'types' => 'owner',
            'store' => 'owner',
            'index' => 'owner',
        ];
    }
}
