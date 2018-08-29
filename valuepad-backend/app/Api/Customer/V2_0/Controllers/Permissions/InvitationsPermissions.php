<?php
namespace ValuePad\Api\Customer\V2_0\Controllers\Permissions;

use Ascope\Libraries\Permissions\AbstractActionsPermissions;

class InvitationsPermissions extends AbstractActionsPermissions
{
	/**
	 * @return array
	 */
	protected function permissions()
	{
		return [
			'store' => 'owner',
			'index' => 'owner',
			'show' => 'owner'
		];
	}
}
