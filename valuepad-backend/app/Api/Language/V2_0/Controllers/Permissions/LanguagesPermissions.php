<?php
namespace ValuePad\Api\Language\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

/**
 *
 *
 */
class LanguagesPermissions extends AbstractActionsPermissions
{

    /**
     *
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => 'all'
        ];
    }
}
