<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Company\V2_0\Protectors\AppraiserProtector;
use ValuePad\Api\Company\V2_0\Protectors\CompanyAdminProtector;

class CompaniesPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'store' => AppraiserProtector::class,
            'update' => CompanyAdminProtector::class,
            'showByTaxId' => 'auth',
        ];
    }
}
