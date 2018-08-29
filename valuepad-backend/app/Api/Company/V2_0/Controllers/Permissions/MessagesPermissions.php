<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class MessagesPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'store' => 'owner',
            'indexByOrder' => 'owner',
            'index' => 'owner',
            'show' => 'owner',
            'markAsRead' => 'owner',
            'markSomeAsRead' => 'owner',
            'markAllAsRead' => 'owner',
            'total' => 'owner'
        ];
    }
}
