<?php
namespace ValuePad\Api\Company\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;
use ValuePad\Api\Company\V2_0\Protectors\CompanyManagerProtector;

class AppraisersPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => CompanyManagerProtector::class,
            'show' => CompanyManagerProtector::class,
            'update' => CompanyManagerProtector::class,
        ];
    }
}