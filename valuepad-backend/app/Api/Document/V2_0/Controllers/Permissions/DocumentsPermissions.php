<?php
namespace ValuePad\Api\Document\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Document\V2_0\Protectors\FriendProtector;

/**
 *
 *
 */
class DocumentsPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'store' => 'all',
            'storeExternal' => FriendProtector::class
        ];
    }
}
