<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Company\V2_0\Protectors\CompanyAdminProtector;
use ValuePad\Api\Company\V2_0\Protectors\CompanyManagerProtector;

class StaffPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => [CompanyAdminProtector::class, CompanyManagerProtector::class],
            'indexByBranch' => [CompanyAdminProtector::class, CompanyManagerProtector::class],
            'storeManager' => CompanyAdminProtector::class,
            'show' => [CompanyAdminProtector::class],
            'destroy' => [CompanyAdminProtector::class],
            'update' => [CompanyAdminProtector::class]
        ];
    }
}
