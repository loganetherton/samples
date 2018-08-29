<?php
namespace ValuePad\Api\Amc\V2_0\Controllers\Permissions;
use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class CreditCardPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'show' => 'owner',
            'replace' => 'owner'
        ];
    }
}
