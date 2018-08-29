<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Company\V2_0\Protectors\CompanyAdminProtector;

class PermissionsPermissions extends AbstractActionsPermissions
{

    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => [CompanyAdminProtector::class],
            'replace' => [CompanyAdminProtector::class]
        ];
    }
}
