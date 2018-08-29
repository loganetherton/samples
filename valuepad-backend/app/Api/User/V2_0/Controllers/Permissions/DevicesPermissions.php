<?php
namespace ValuePad\Api\User\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class DevicesPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'store' => 'owner',
			'destroy' => 'owner'
		];
	}
}
