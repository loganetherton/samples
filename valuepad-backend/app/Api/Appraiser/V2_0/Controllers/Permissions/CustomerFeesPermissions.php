<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class CustomerFeesPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'index' => 'owner',
			'store' => 'owner',
			'update' => 'owner',
			'updateBulk' => 'owner',
			'destroy' => 'owner',
			'destroyBulk' => 'owner'
		];
	}
}
