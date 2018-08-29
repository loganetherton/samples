<?php
namespace ValuePad\Api\Customer\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class AppraisersPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
            'logs' => 'owner',
			'index' => 'owner',
			'show' => 'owner',
			'ach' => 'owner',
            'settings' => 'owner'
		];
	}
}
