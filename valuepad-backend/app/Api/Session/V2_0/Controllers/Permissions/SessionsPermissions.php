<?php
namespace ValuePad\Api\Session\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Session\V2_0\Protectors\AutoLoginProtector;
use ValuePad\Api\Session\V2_0\Protectors\SessionProtector;

/**
 *
 *
 */
class SessionsPermissions extends AbstractActionsPermissions
{

    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'store' => 'all',
            'show' => SessionProtector::class,
            'destroy' => SessionProtector::class,
            'destroyAll' => SessionProtector::class,
			'storeAutoLoginToken' => AutoLoginProtector::class,
            'refresh' => SessionProtector::class
        ];
    }
}
