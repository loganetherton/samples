<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Company\V2_0\Protectors\CompanyAdminProtector;
use ValuePad\Api\Company\V2_0\Protectors\CompanyManagerProtector;

class InvitationsPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => [CompanyAdminProtector::class, CompanyManagerProtector::class],
            'store' => [CompanyAdminProtector::class, CompanyManagerProtector::class]
        ];
    }
}
