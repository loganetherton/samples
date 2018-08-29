<?php
namespace ValuePad\Api\Customer\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class FeesPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'index' => 'owner'
		];
	}
}
