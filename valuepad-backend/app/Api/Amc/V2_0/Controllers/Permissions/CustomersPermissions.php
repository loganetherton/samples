<?php
namespace ValuePad\Api\Amc\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class CustomersPermissions extends AbstractActionsPermissions
{
    /**
     * @return array
     */
    protected function permissions()
    {
        return [
            'index' => 'owner',
            'listAdditionalStatuses' => 'owner',
            'listAdditionalDocumentsTypes' => 'owner',
        ];
    }
}
